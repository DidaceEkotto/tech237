<?php

namespace App\Console\Commands;

use App\Models\HeartbeatMonitor;
use App\Services\HeartbeatService;
use Illuminate\Console\Command;

class CheckHeartbeatMonitors extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'heartbeat:check {--force : Exécuter immédiatement le ping sur tous les moniteurs actifs sans attendre la date planifiée}';

    /**
     * The console command description.
     */
    protected $description = 'Exécute les pings de surveillance de Tech237 Heartbeat et alerte en cas d\'interruption';

    /**
     * Execute the console command.
     */
    public function handle(HeartbeatService $service)
    {
        $force = (bool) $this->option('force');

        $query = HeartbeatMonitor::where('is_active', true);

        if (!$force) {
            $query->where(function ($q) {
                $q->whereNull('next_check_at')
                  ->orWhere('next_check_at', '<=', now());
            });
        }

        $monitors = $query->get();

        if ($monitors->isEmpty()) {
            $this->info("Tech237 Heartbeat: Aucun moniteur en attente de vérification.");
            return self::SUCCESS;
        }

        $this->info("Tech237 Heartbeat: Début de la vérification de {$monitors->count()} moniteur(s)...");

        $upCount = 0;
        $downCount = 0;

        foreach ($monitors as $monitor) {
            $this->line(" → Vérification de [{$monitor->name}] ({$monitor->url})...");
            try {
                $res = $service->ping($monitor, true);
                if ($res['is_up']) {
                    $upCount++;
                    $this->info("   ✔ EN LIGNE ({$res['status_code']}) en {$res['response_time_ms']}ms");
                } else {
                    $downCount++;
                    $this->error("   ✖ HORS LIGNE: {$res['error_message']}");
                }
            } catch (\Throwable $e) {
                $downCount++;
                $this->error("   ✖ Exception: " . $e->getMessage());
            }
        }

        $this->info("Tech237 Heartbeat: Fin. {$upCount} en ligne, {$downCount} hors-ligne.");
        return self::SUCCESS;
    }
}
