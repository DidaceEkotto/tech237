<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\HeartbeatMonitor;
use App\Services\HeartbeatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class HeartbeatController extends Controller
{
    protected HeartbeatService $heartbeatService;

    public function __construct(HeartbeatService $heartbeatService)
    {
        $this->heartbeatService = $heartbeatService;
    }

    /**
     * Get all monitors for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = Auth::guard('web')->user();

        $monitors = HeartbeatMonitor::where('user_id', $user->id)
            ->with(['project:id,name', 'recentChecks'])
            ->latest()
            ->get();

        $totalMonitors = $monitors->count();
        $upMonitors = $monitors->where('status', 'up')->count();
        $downMonitors = $monitors->where('status', 'down')->count();
        $pausedMonitors = $monitors->where('is_active', false)->count();

        $activeUp = $monitors->where('status', 'up')->filter(fn($m) => !is_null($m->last_response_time_ms));
        $avgLatency = $activeUp->count() > 0 ? (int) round($activeUp->avg('last_response_time_ms')) : 0;
        $avgUptime = $totalMonitors > 0 ? round($monitors->avg('uptime_percentage'), 2) : 100.00;

        return response()->json([
            'success' => true,
            'monitors' => $monitors,
            'stats' => [
                'total' => $totalMonitors,
                'up' => $upMonitors,
                'down' => $downMonitors,
                'paused' => $pausedMonitors,
                'avg_latency_ms' => $avgLatency,
                'avg_uptime_pct' => $avgUptime,
            ],
        ]);
    }

    /**
     * Store a new site monitor and perform an initial ping.
     */
    public function store(Request $request)
    {
        $user = Auth::guard('web')->user();

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'url' => 'required|string|max:500',
            'project_id' => 'nullable|exists:projects,id',
            'check_interval_minutes' => 'nullable|integer|in:1,3,5,10,15,30,60',
            'expected_status_code' => 'nullable|integer|min:100|max:599',
            'http_method' => 'nullable|string|in:GET,HEAD,POST',
            'timeout_seconds' => 'nullable|integer|min:2|max:30',
            'notify_email' => 'nullable|boolean',
            'email_recipient' => 'nullable|email|max:150',
            'is_public' => 'nullable|boolean',
        ], [
            'name.required' => 'Le nom du projet ou du site est obligatoire.',
            'url.required' => 'L\'URL à surveiller est obligatoire.',
            'email_recipient.email' => 'Veuillez saisir une adresse email valide pour les alertes.',
        ]);

        $url = trim($validated['url']);
        if (!preg_match('~^https?://~i', $url)) {
            $url = 'https://' . $url;
        }
        $validated['url'] = $url;
        $validated['user_id'] = $user->id;
        $validated['uuid'] = (string) Str::uuid();
        $validated['status'] = 'pending';
        $validated['is_active'] = true;
        $validated['check_interval_minutes'] = $validated['check_interval_minutes'] ?? 5;
        $validated['expected_status_code'] = $validated['expected_status_code'] ?? 200;
        $validated['http_method'] = $validated['http_method'] ?? 'GET';
        $validated['timeout_seconds'] = $validated['timeout_seconds'] ?? 10;
        $validated['notify_email'] = isset($validated['notify_email']) ? (bool)$validated['notify_email'] : true;

        $monitor = HeartbeatMonitor::create($validated);

        // Perform immediate first ping without triggering down emails immediately
        try {
            $pingRes = $this->heartbeatService->ping($monitor, false);
        } catch (\Throwable $e) {
            $pingRes = ['is_up' => false, 'error' => $e->getMessage()];
        }

        return response()->json([
            'success' => true,
            'message' => 'Nouveau site ajouté à Tech237 Heartbeat ! Le premier diagnostic a été exécuté.',
            'monitor' => $monitor->fresh(['project:id,name', 'recentChecks']),
            'ping_result' => $pingRes,
        ]);
    }

    /**
     * Update an existing monitor.
     */
    public function update(Request $request, int $id)
    {
        $user = Auth::guard('web')->user();
        $monitor = HeartbeatMonitor::where('id', $id)->where('user_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'url' => 'required|string|max:500',
            'project_id' => 'nullable|exists:projects,id',
            'check_interval_minutes' => 'nullable|integer|in:1,3,5,10,15,30,60',
            'expected_status_code' => 'nullable|integer|min:100|max:599',
            'http_method' => 'nullable|string|in:GET,HEAD,POST',
            'timeout_seconds' => 'nullable|integer|min:2|max:30',
            'notify_email' => 'nullable|boolean',
            'email_recipient' => 'nullable|email|max:150',
            'is_public' => 'nullable|boolean',
        ]);

        $url = trim($validated['url']);
        if (!preg_match('~^https?://~i', $url)) {
            $url = 'https://' . $url;
        }
        $validated['url'] = $url;

        $urlChanged = $monitor->url !== $url;
        $monitor->update($validated);

        if ($urlChanged && $monitor->is_active) {
            try {
                $this->heartbeatService->ping($monitor, false);
            } catch (\Throwable $e) {
                // Ignore initial ping error
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Paramètres de surveillance mis à jour avec succès.',
            'monitor' => $monitor->fresh(['project:id,name', 'recentChecks']),
        ]);
    }

    /**
     * Delete a monitor and its history.
     */
    public function destroy(int $id)
    {
        $user = Auth::guard('web')->user();
        $monitor = HeartbeatMonitor::where('id', $id)->where('user_id', $user->id)->firstOrFail();

        $monitor->delete();

        return response()->json([
            'success' => true,
            'message' => 'Le site a été retiré de la surveillance Tech237 Heartbeat.',
        ]);
    }

    /**
     * Trigger a manual instant ping on a monitor.
     */
    public function pingNow(int $id)
    {
        $user = Auth::guard('web')->user();
        $monitor = HeartbeatMonitor::where('id', $id)->where('user_id', $user->id)->firstOrFail();

        $result = $this->heartbeatService->ping($monitor, true);

        return response()->json([
            'success' => true,
            'message' => $result['is_up'] 
                ? "Ping réussi ! Temps de réponse : {$result['response_time_ms']} ms (HTTP {$result['status_code']})"
                : "Attention : Le site est injoignable ({$result['error_message']})",
            'monitor' => $monitor->fresh(['project:id,name', 'recentChecks']),
            'result' => $result,
        ]);
    }

    /**
     * Toggle active/paused state of a monitor.
     */
    public function toggleActive(int $id)
    {
        $user = Auth::guard('web')->user();
        $monitor = HeartbeatMonitor::where('id', $id)->where('user_id', $user->id)->firstOrFail();

        $newState = !$monitor->is_active;
        $update = ['is_active' => $newState];

        if ($newState) {
            $update['status'] = 'pending';
            $update['next_check_at'] = now();
        } else {
            $update['status'] = 'paused';
        }

        $monitor->update($update);

        if ($newState) {
            try {
                $this->heartbeatService->ping($monitor, false);
            } catch (\Throwable $e) {}
        }

        return response()->json([
            'success' => true,
            'message' => $newState 
                ? 'Surveillance réactivée avec succès.' 
                : 'Surveillance mise en pause.',
            'monitor' => $monitor->fresh(['project:id,name', 'recentChecks']),
        ]);
    }

    /**
     * Send a test email alert to verify configuration.
     */
    public function sendTestAlert(int $id)
    {
        $user = Auth::guard('web')->user();
        $monitor = HeartbeatMonitor::where('id', $id)->where('user_id', $user->id)->firstOrFail();

        $sent = $this->heartbeatService->sendTestAlert($monitor);

        if ($sent) {
            return response()->json([
                'success' => true,
                'message' => "Un email de test a été envoyé à {$monitor->effective_email}.",
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => "Impossible d'envoyer l'email de test. Vérifiez la configuration email.",
        ], 500);
    }

    /**
     * Public embeddable SVG status badge.
     */
    public function badge(string $uuid)
    {
        $monitor = HeartbeatMonitor::where('uuid', $uuid)->first();
        if (!$monitor) {
            return response('<svg xmlns="http://www.w3.org/2000/svg" width="130" height="20"><rect width="130" height="20" fill="#555"/><text x="10" y="14" fill="#fff" font-size="11">Tech237: 404</text></svg>', 404)
                ->header('Content-Type', 'image/svg+xml');
        }

        $svg = $this->heartbeatService->generateBadgeSvg($monitor);

        return response($svg, 200)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
