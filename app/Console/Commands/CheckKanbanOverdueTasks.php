<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\KanbanCard;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class CheckKanbanOverdueTasks extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'kanban:check-overdue';

    /**
     * The console command description.
     */
    protected $description = 'Vérifie les tâches Kanban ayant dépassé leur date limite et alerte par email les contributeurs du projet';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now()->toDateString();

        $overdueCards = KanbanCard::whereNotNull('due_date')
            ->where('due_date', '<', $today)
            ->whereNull('overdue_alert_sent_at')
            ->whereHas('column', function ($q) {
                $q->where('is_done_column', false);
            })
            ->with([
                'board.project.user',
                'board.project.collaborators.user',
                'assignee',
            ])
            ->get();

        $count = 0;

        foreach ($overdueCards as $card) {
            $board = $card->board;
            $project = $board?->project;
            $recipients = collect();

            // 1. Assignee
            if ($card->assignee && $card->assignee->email) {
                $recipients->push($card->assignee);
            }

            // 2. Project Owner
            if ($project?->user && $project->user->email) {
                $recipients->push($project->user);
            } elseif ($board?->user && $board->user->email) {
                $recipients->push($board->user);
            }

            // 3. Project Collaborators
            if ($project && $project->collaborators) {
                foreach ($project->collaborators as $collab) {
                    if ($collab->user && $collab->user->email) {
                        $recipients->push($collab->user);
                    }
                }
            }

            // Unique recipients
            $recipients = $recipients->unique('id');

            foreach ($recipients as $user) {
                try {
                    Mail::send('emails.kanban_task_overdue', [
                        'user' => $user,
                        'card' => $card,
                        'board' => $board,
                        'project' => $project,
                        'kanbanUrl' => route('user.kanban.show', $board->id),
                    ], function ($m) use ($user, $card, $project) {
                        $projectName = $project ? $project->name : 'Tech237';
                        $m->to($user->email)
                          ->subject("⚠️ Échéance dépassée : Tâche \"{$card->title}\" sur {$projectName}");
                    });
                } catch (\Throwable $e) {
                    Log::error("Erreur envoi email retard Kanban: " . $e->getMessage());
                }
            }

            $card->update(['overdue_alert_sent_at' => now()]);
            $count++;
        }

        if ($this->output) {
            $this->info("{$count} tâche(s) en retard vérifiée(s) et alertée(s).");
        }
        return Command::SUCCESS;
    }
}
