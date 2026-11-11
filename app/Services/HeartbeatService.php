<?php

namespace App\Services;

use App\Models\HeartbeatCheck;
use App\Models\HeartbeatMonitor;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class HeartbeatService
{
    /**
     * Perform an immediate ping / check on a monitor.
     */
    public function ping(HeartbeatMonitor $monitor, bool $triggerAlerts = true): array
    {
        $url = trim($monitor->url);
        if (!preg_match('~^https?://~i', $url)) {
            $url = 'https://' . $url;
        }

        $method = strtoupper($monitor->http_method ?: 'GET');
        $timeout = $monitor->timeout_seconds ?: 10;
        $startTime = microtime(true);
        $statusCode = null;
        $isUp = false;
        $errorMessage = null;
        $ipAddress = null;

        try {
            $parsedHost = parse_url($url, PHP_URL_HOST);
            if ($parsedHost) {
                $dnsIp = @gethostbyname($parsedHost);
                if ($dnsIp && $dnsIp !== $parsedHost) {
                    $ipAddress = $dnsIp;
                }
            }
        } catch (Throwable $e) {
            // DNS resolution quiet fail
        }

        try {
            $response = Http::timeout($timeout)
                ->withHeaders([
                    'User-Agent' => 'Tech237-Heartbeat-Bot/1.0 (+https://tech237.net/heartbeat)',
                    'Accept' => '*/*',
                ])
                ->withoutVerifying() // Avoid failing on dev self-signed SSL certificates
                ->send($method, $url);

            $endTime = microtime(true);
            $latencyMs = (int) round(($endTime - $startTime) * 1000);
            $statusCode = $response->status();

            // Up criteria: 2xx or 3xx status, or matches custom expected status code
            if ($monitor->expected_status_code && $statusCode === (int)$monitor->expected_status_code) {
                $isUp = true;
            } elseif ($statusCode >= 200 && $statusCode < 400) {
                $isUp = true;
            } elseif ($statusCode >= 400 && $statusCode < 500) {
                // 401 Unauthorized or 403 Forbidden means webserver is online and answering
                $isUp = true;
            } else {
                $isUp = false;
                $errorMessage = "Code HTTP inattendu : {$statusCode}";
            }
        } catch (Throwable $e) {
            $endTime = microtime(true);
            $latencyMs = (int) round(($endTime - $startTime) * 1000);
            $isUp = false;
            $rawMsg = $e->getMessage();

            if (str_contains($rawMsg, 'cURL error 28') || str_contains(strtolower($rawMsg), 'timed out')) {
                $errorMessage = "Délai d'attente dépassé (Timeout après {$timeout}s)";
            } elseif (str_contains($rawMsg, 'cURL error 6') || str_contains(strtolower($rawMsg), 'could not resolve host')) {
                $errorMessage = "Impossible de résoudre le nom de domaine (DNS introuvable)";
            } elseif (str_contains($rawMsg, 'cURL error 7') || str_contains(strtolower($rawMsg), 'connection refused')) {
                $errorMessage = "Connexion refusée par le serveur cible";
            } else {
                $errorMessage = "Erreur de connexion : " . substr($rawMsg, 0, 150);
            }
        }

        // 1. Record Check entry
        HeartbeatCheck::create([
            'heartbeat_monitor_id' => $monitor->id,
            'status_code' => $statusCode,
            'response_time_ms' => $latencyMs,
            'is_up' => $isUp,
            'error_message' => $errorMessage,
            'ip_address' => $ipAddress,
            'checked_at' => now(),
        ]);

        // 2. Prune old checks (keep last 40 per monitor)
        try {
            $checksCount = HeartbeatCheck::where('heartbeat_monitor_id', $monitor->id)->count();
            if ($checksCount > 40) {
                $oldestId = HeartbeatCheck::where('heartbeat_monitor_id', $monitor->id)
                    ->latest('id')
                    ->skip(40)
                    ->value('id');
                if ($oldestId) {
                    HeartbeatCheck::where('heartbeat_monitor_id', $monitor->id)
                        ->where('id', '<=', $oldestId)
                        ->delete();
                }
            }
        } catch (Throwable $e) {
            // Prune error ignored
        }

        // 3. Status transitions & Email alerts
        $wasDown = $monitor->is_currently_down;
        $totalChecks = $monitor->total_checks + 1;
        $successfulChecks = $monitor->successful_checks + ($isUp ? 1 : 0);
        $failedChecks = $monitor->failed_checks + (!$isUp ? 1 : 0);
        $uptimePct = $totalChecks > 0 ? round(($successfulChecks / $totalChecks) * 100, 2) : 100.00;

        $updateData = [
            'last_status_code' => $statusCode,
            'last_response_time_ms' => $latencyMs,
            'last_checked_at' => now(),
            'next_check_at' => now()->addMinutes($monitor->check_interval_minutes ?: 5),
            'total_checks' => $totalChecks,
            'successful_checks' => $successfulChecks,
            'failed_checks' => $failedChecks,
            'uptime_percentage' => $uptimePct,
        ];

        if ($isUp) {
            $updateData['status'] = 'up';
            $updateData['last_up_at'] = now();
            $updateData['last_error_message'] = null;
            $updateData['is_currently_down'] = false;

            // Trigger recovery alert if it was down before
            if ($wasDown && $triggerAlerts && $monitor->notify_email) {
                $this->sendRecoveryAlert($monitor, $latencyMs);
                $updateData['down_alert_sent_at'] = null;
            }
        } else {
            $updateData['status'] = 'down';
            $updateData['last_down_at'] = now();
            $updateData['last_error_message'] = $errorMessage;
            $updateData['is_currently_down'] = true;

            // Trigger down alert only on state change (or if alert hasn't been sent yet)
            if (!$wasDown && $triggerAlerts && $monitor->notify_email) {
                $this->sendDownAlert($monitor, $errorMessage ?: 'Le site ne répond plus.');
                $updateData['down_alert_sent_at'] = now();
            }
        }

        $monitor->update($updateData);

        return [
            'success' => true,
            'is_up' => $isUp,
            'status_code' => $statusCode,
            'response_time_ms' => $latencyMs,
            'error_message' => $errorMessage,
            'checked_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Send email alert when a monitored site goes down.
     */
    public function sendDownAlert(HeartbeatMonitor $monitor, string $reason): void
    {
        $recipient = $monitor->effective_email;
        if (!$recipient) return;

        $siteName = $monitor->name ?: $monitor->url;
        $url = $monitor->url;
        $timeStr = now()->format('d/m/Y à H:i:s');

        try {
            Mail::send('emails.heartbeat_down', [
                'monitor' => $monitor,
                'siteName' => $siteName,
                'url' => $url,
                'reason' => $reason,
                'timeStr' => $timeStr,
                'dashboardUrl' => url('/espace-utilisateur?section=heartbeat'),
            ], function ($m) use ($recipient, $siteName) {
                $m->to($recipient)
                  ->subject("🚨 [Alerte Tech237 Heartbeat] Votre site {$siteName} est Hors-Ligne !");
            });
            Log::info("Tech237 Heartbeat: Down alert sent to {$recipient} for monitor {$monitor->id} ({$url})");
        } catch (Throwable $e) {
            Log::error("Tech237 Heartbeat: Failed to send down alert to {$recipient}: " . $e->getMessage());
        }
    }

    /**
     * Send email alert when a monitored site recovers.
     */
    public function sendRecoveryAlert(HeartbeatMonitor $monitor, int $latencyMs): void
    {
        $recipient = $monitor->effective_email;
        if (!$recipient) return;

        $siteName = $monitor->name ?: $monitor->url;
        $url = $monitor->url;
        $timeStr = now()->format('d/m/Y à H:i:s');

        try {
            Mail::send('emails.heartbeat_up', [
                'monitor' => $monitor,
                'siteName' => $siteName,
                'url' => $url,
                'latencyMs' => $latencyMs,
                'timeStr' => $timeStr,
                'dashboardUrl' => url('/espace-utilisateur?section=heartbeat'),
            ], function ($m) use ($recipient, $siteName) {
                $m->to($recipient)
                  ->subject("✅ [Résolu Tech237 Heartbeat] Votre site {$siteName} est de nouveau En Ligne !");
            });
            Log::info("Tech237 Heartbeat: Recovery alert sent to {$recipient} for monitor {$monitor->id} ({$url})");
        } catch (Throwable $e) {
            Log::error("Tech237 Heartbeat: Failed to send recovery alert to {$recipient}: " . $e->getMessage());
        }
    }

    /**
     * Send a test alert email to confirm user notification settings.
     */
    public function sendTestAlert(HeartbeatMonitor $monitor): bool
    {
        $recipient = $monitor->effective_email;
        if (!$recipient) return false;

        $siteName = $monitor->name ?: $monitor->url;
        $url = $monitor->url;
        $timeStr = now()->format('d/m/Y à H:i:s');

        try {
            Mail::send('emails.heartbeat_down', [
                'monitor' => $monitor,
                'siteName' => $siteName,
                'url' => $url,
                'reason' => 'Test de notification manuel déclenché depuis votre espace utilisateur.',
                'timeStr' => $timeStr,
                'isTest' => true,
                'dashboardUrl' => url('/espace-utilisateur?section=heartbeat'),
            ], function ($m) use ($recipient, $siteName) {
                $m->to($recipient)
                  ->subject("🔔 [Test Tech237 Heartbeat] Alerte de test pour {$siteName}");
            });
            return true;
        } catch (Throwable $e) {
            Log::error("Tech237 Heartbeat: Failed to send test alert to {$recipient}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate an embeddable SVG status badge for GitHub READMEs or websites.
     */
    public function generateBadgeSvg(HeartbeatMonitor $monitor): string
    {
        $isUp = $monitor->status === 'up';
        $statusText = $isUp ? 'en ligne' : ($monitor->status === 'down' ? 'hors ligne' : 'en attente');
        $statusColor = $isUp ? '#10b981' : ($monitor->status === 'down' ? '#ef4444' : '#6b7280');
        $uptime = number_format($monitor->uptime_percentage ?: 100, 1) . '%';
        $latency = $monitor->last_response_time_ms ? $monitor->last_response_time_ms . 'ms' : '';

        $rightText = "{$statusText} • {$uptime}";
        if ($latency && $isUp) {
            $rightText = "{$statusText} • {$latency}";
        }

        $leftText = 'Tech237 Heartbeat';
        $leftWidth = 120;
        $rightWidth = 110;
        $totalWidth = $leftWidth + $rightWidth;

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="{$totalWidth}" height="20" role="img" aria-label="{$leftText}: {$rightText}">
  <title>{$leftText}: {$rightText}</title>
  <linearGradient id="s" x2="0" y2="100%">
    <stop offset="0" stop-color="#bbb" stop-opacity=".1"/>
    <stop offset="1" stop-opacity=".1"/>
  </linearGradient>
  <clipPath id="r">
    <rect width="{$totalWidth}" height="20" rx="3" fill="#fff"/>
  </clipPath>
  <g clip-path="url(#r)">
    <rect width="{$leftWidth}" height="20" fill="#1f2937"/>
    <rect x="{$leftWidth}" width="{$rightWidth}" height="20" fill="{$statusColor}"/>
    <rect width="{$totalWidth}" height="20" fill="url(#s)"/>
  </g>
  <g fill="#fff" text-anchor="middle" font-family="Verdana,Geneva,DejaVu Sans,sans-serif" text-rendering="geometricPrecision" font-size="110">
    <text aria-hidden="true" x="600" y="150" fill="#010101" fill-opacity=".3" transform="scale(.1)" textLength="1100">{$leftText}</text>
    <text x="600" y="140" transform="scale(.1)" fill="#fff" textLength="1100">{$leftText}</text>
    <text aria-hidden="true" x="1750" y="150" fill="#010101" fill-opacity=".3" transform="scale(.1)" textLength="1000">{$rightText}</text>
    <text x="1750" y="140" transform="scale(.1)" fill="#fff" textLength="1000">{$rightText}</text>
  </g>
</svg>
SVG;
    }
}
