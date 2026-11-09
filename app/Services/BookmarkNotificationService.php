<?php

namespace App\Services;

use App\Models\Article;
use App\Models\AppReview;
use App\Models\GameReview;
use App\Models\Project;
use App\Models\ProjectTimeline;
use App\Models\UserBookmark;
use App\Models\UserMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class BookmarkNotificationService
{
    /**
     * Notify users who favorited this project about a new devlog/timeline step.
     */
    public static function notifyProjectDevlog(Project $project, ProjectTimeline $timeline): void
    {
        try {
            $bookmarks = UserBookmark::with('user')
                ->where('bookmarkable_type', 'project')
                ->where('bookmarkable_id', $project->id)
                ->get();

            if ($bookmarks->isEmpty()) {
                return;
            }

            $projectUrl = route('projects.show', $project->slug ?? $project->id) . '#devlogs';

            foreach ($bookmarks as $bm) {
                $subscriber = $bm->user;
                if (!$subscriber || empty($subscriber->email)) {
                    continue;
                }

                // In-App Notification
                try {
                    UserMessage::create([
                        'user_id' => $subscriber->id,
                        'sender_id' => $project->user_id ?? null,
                        'subject' => '🚀 Nouveau Devlog sur ' . $project->name,
                        'message' => "Une nouvelle mise à jour a été publiée sur le projet « {$project->name} » : « {$timeline->title} ».\n\nConsultez le devlog ici : " . $projectUrl,
                        'is_read' => false,
                    ]);
                } catch (\Throwable $e) {
                    Log::warning("In-app devlog notification failed for user {$subscriber->id}: " . $e->getMessage());
                }

                // Email Notification
                try {
                    Mail::send('emails.project_devlog_notification', [
                        'subscriber' => $subscriber,
                        'project' => $project,
                        'timeline' => $timeline,
                        'projectUrl' => $projectUrl,
                    ], function ($m) use ($subscriber, $project, $timeline) {
                        $m->to($subscriber->email, $subscriber->name)
                          ->subject("🚀 Nouveau Devlog : {$project->name} - {$timeline->title}");
                    });
                } catch (\Throwable $e) {
                    Log::warning("Email devlog notification failed for user {$subscriber->id}: " . $e->getMessage());
                }
            }
        } catch (\Throwable $e) {
            Log::error("Error in BookmarkNotificationService::notifyProjectDevlog: " . $e->getMessage());
        }
    }

    /**
     * Notify users who favorited the Games rubric ("Tests Jeux Vidéo").
     */
    public static function notifyGameReview(GameReview $review): void
    {
        try {
            $bookmarks = UserBookmark::with('user')
                ->where('bookmarkable_type', 'games_rubrique')
                ->get();

            if ($bookmarks->isEmpty()) {
                return;
            }

            $reviewUrl = route('games.show', $review->slug);
            $reviewImage = $review->cover_image ? (str_starts_with($review->cover_image, 'http') ? $review->cover_image : url($review->cover_image)) : null;

            foreach ($bookmarks as $bm) {
                $subscriber = $bm->user;
                if (!$subscriber || empty($subscriber->email)) {
                    continue;
                }

                // In-App Notification
                try {
                    UserMessage::create([
                        'user_id' => $subscriber->id,
                        'subject' => '🎮 Nouveau Test Jeu Vidéo : ' . $review->game_name,
                        'message' => "Le test complet de « {$review->game_name} » vient d'être mis en ligne sur Tech237 avec la note de " . ($review->final_score ?? 'N/A') . "/20.\n\nLire le test : " . $reviewUrl,
                        'is_read' => false,
                    ]);
                } catch (\Throwable $e) {
                    Log::warning("In-app game review notification failed for user {$subscriber->id}: " . $e->getMessage());
                }

                // Email Notification
                try {
                    Mail::send('emails.review_notification', [
                        'subscriber' => $subscriber,
                        'reviewTitle' => $review->game_name,
                        'rubriqueName' => 'Tests Jeux Vidéo',
                        'badgeText' => '🎮 TEST JEU VIDÉO',
                        'categoryBadge' => $review->platform_category ?? 'Gaming',
                        'score' => $review->final_score,
                        'summary' => $review->summary_fr ?? $review->summary_en ?? Str::limit(strip_tags($review->content_fr ?? ''), 200),
                        'reviewImage' => $reviewImage,
                        'reviewUrl' => $reviewUrl,
                    ], function ($m) use ($subscriber, $review) {
                        $m->to($subscriber->email, $subscriber->name)
                          ->subject("🎮 Nouveau Test Jeu Vidéo : {$review->game_name}");
                    });
                } catch (\Throwable $e) {
                    Log::warning("Email game review notification failed for user {$subscriber->id}: " . $e->getMessage());
                }
            }
        } catch (\Throwable $e) {
            Log::error("Error in BookmarkNotificationService::notifyGameReview: " . $e->getMessage());
        }
    }

    /**
     * Notify users who favorited the Apps rubric ("Tests Applications").
     */
    public static function notifyAppReview(AppReview $review): void
    {
        try {
            $bookmarks = UserBookmark::with('user')
                ->where('bookmarkable_type', 'apps_rubrique')
                ->get();

            if ($bookmarks->isEmpty()) {
                return;
            }

            $reviewUrl = route('apps.show', $review->slug);
            $reviewImage = $review->cover_image ? (str_starts_with($review->cover_image, 'http') ? $review->cover_image : url($review->cover_image)) : null;

            foreach ($bookmarks as $bm) {
                $subscriber = $bm->user;
                if (!$subscriber || empty($subscriber->email)) {
                    continue;
                }

                // In-App Notification
                try {
                    UserMessage::create([
                        'user_id' => $subscriber->id,
                        'subject' => '📱 Nouveau Test Application : ' . $review->app_name,
                        'message' => "Le test de l'application « {$review->app_name} » est maintenant disponible sur Tech237 avec la note de " . ($review->final_score ?? 'N/A') . "/20.\n\nDécouvrir l'analyse : " . $reviewUrl,
                        'is_read' => false,
                    ]);
                } catch (\Throwable $e) {
                    Log::warning("In-app app review notification failed for user {$subscriber->id}: " . $e->getMessage());
                }

                // Email Notification
                try {
                    Mail::send('emails.review_notification', [
                        'subscriber' => $subscriber,
                        'reviewTitle' => $review->app_name,
                        'rubriqueName' => 'Tests Applications',
                        'badgeText' => '📱 TEST APPLICATION',
                        'categoryBadge' => $review->category_name ?? 'Application',
                        'score' => $review->final_score,
                        'summary' => $review->summary_fr ?? $review->summary_en ?? Str::limit(strip_tags($review->content_fr ?? ''), 200),
                        'reviewImage' => $reviewImage,
                        'reviewUrl' => $reviewUrl,
                    ], function ($m) use ($subscriber, $review) {
                        $m->to($subscriber->email, $subscriber->name)
                          ->subject("📱 Nouveau Test Application : {$review->app_name}");
                    });
                } catch (\Throwable $e) {
                    Log::warning("Email app review notification failed for user {$subscriber->id}: " . $e->getMessage());
                }
            }
        } catch (\Throwable $e) {
            Log::error("Error in BookmarkNotificationService::notifyAppReview: " . $e->getMessage());
        }
    }

    /**
     * Notify users who favorited the article's Rubrique or Category.
     */
    public static function notifyArticlePublished(Article $article): void
    {
        try {
            // Only send if article is published
            if ($article->status !== 'published') {
                return;
            }

            // Eager load relations if not loaded
            if (!$article->relationLoaded('rubrique')) {
                $article->load('rubrique');
            }
            if (!$article->relationLoaded('category')) {
                $article->load('category');
            }

            $rubriqueId = $article->rubrique_id;
            $categoryId = $article->category_id;

            if (!$rubriqueId && !$categoryId) {
                return;
            }

            // Query bookmarks for this rubrique or category
            $query = UserBookmark::with('user');
            $query->where(function ($q) use ($rubriqueId, $categoryId) {
                if ($rubriqueId) {
                    $q->where(function ($sub) use ($rubriqueId) {
                        $sub->where('bookmarkable_type', 'rubrique')
                            ->where('bookmarkable_id', $rubriqueId);
                    });
                }
                if ($categoryId) {
                    if ($rubriqueId) {
                        $q->orWhere(function ($sub) use ($categoryId) {
                            $sub->where('bookmarkable_type', 'category')
                                ->where('bookmarkable_id', $categoryId);
                        });
                    } else {
                        $q->where('bookmarkable_type', 'category')
                          ->where('bookmarkable_id', $categoryId);
                    }
                }
            });

            $bookmarks = $query->get();
            if ($bookmarks->isEmpty()) {
                return;
            }

            $articleUrl = route('article.show', $article->slug);
            $articleImage = $article->featured_image ? (str_starts_with($article->featured_image, 'http') ? $article->featured_image : url($article->featured_image)) : null;
            $articleTitle = $article->title_fr ?? $article->title_en ?? 'Nouvel article';
            $summary = $article->summary_fr ?? $article->summary_en ?? Str::limit(strip_tags($article->content_fr ?? ''), 220);

            // Deduplicate by user_id
            $sentUsers = [];

            foreach ($bookmarks as $bm) {
                $subscriber = $bm->user;
                if (!$subscriber || empty($subscriber->email) || isset($sentUsers[$subscriber->id])) {
                    continue;
                }
                $sentUsers[$subscriber->id] = true;

                $targetTypeLabel = $bm->bookmarkable_type === 'category' ? 'Catégorie' : 'Rubrique';
                $targetName = $bm->bookmarkable_type === 'category' 
                    ? ($article->category?->name_fr ?? $article->category?->name ?? 'Catégorie')
                    : ($article->rubrique?->name_fr ?? $article->rubrique?->name ?? 'Rubrique');

                // In-App Notification
                try {
                    UserMessage::create([
                        'user_id' => $subscriber->id,
                        'subject' => "📰 Nouvel article dans {$targetName} : {$articleTitle}",
                        'message' => "Un nouvel article vient d'être publié dans votre {$targetTypeLabel} favorite « {$targetName} » :\n\n{$articleTitle}\n\nLire l'article : {$articleUrl}",
                        'is_read' => false,
                    ]);
                } catch (\Throwable $e) {
                    Log::warning("In-app article notification failed for user {$subscriber->id}: " . $e->getMessage());
                }

                // Email Notification
                try {
                    Mail::send('emails.content_notification', [
                        'subscriber' => $subscriber,
                        'articleTitle' => $articleTitle,
                        'targetTypeLabel' => $targetTypeLabel,
                        'targetName' => $targetName,
                        'summary' => $summary,
                        'articleImage' => $articleImage,
                        'articleUrl' => $articleUrl,
                    ], function ($m) use ($subscriber, $targetName, $articleTitle) {
                        $m->to($subscriber->email, $subscriber->name)
                          ->subject("📰 [{$targetName}] Nouveau : {$articleTitle}");
                    });
                } catch (\Throwable $e) {
                    Log::warning("Email article notification failed for user {$subscriber->id}: " . $e->getMessage());
                }
            }
        } catch (\Throwable $e) {
            Log::error("Error in BookmarkNotificationService::notifyArticlePublished: " . $e->getMessage());
        }
    }
}
