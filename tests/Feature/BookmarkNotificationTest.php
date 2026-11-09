<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\AppReview;
use App\Models\GameReview;
use App\Models\Project;
use App\Models\ProjectTimeline;
use App\Models\Rubrique;
use App\Models\Category;
use App\Models\User;
use App\Models\UserBookmark;
use App\Services\BookmarkNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class BookmarkNotificationTest extends TestCase
{
    public function test_email_views_render_successfully()
    {
        $user = new User([
            'id' => 1,
            'name' => 'Jean Test',
            'email' => 'jean@example.com',
        ]);

        $project = new Project([
            'id' => 1,
            'name' => 'PayCam Mobile',
            'slug' => 'paycam-mobile',
            'description' => 'Agrégateur de paiement mobile money',
        ]);

        $timeline = new ProjectTimeline([
            'id' => 1,
            'title' => 'Intégration API Orange Money v2',
            'description' => 'Mise en place des webhooks sécurisés et idempotence.',
            'solution_applied' => 'Validation SHA256 avec retry automatique.',
        ]);

        // Render project devlog email
        $devlogHtml = View::make('emails.project_devlog_notification', [
            'subscriber' => $user,
            'project' => $project,
            'timeline' => $timeline,
            'projectUrl' => 'https://tech237.net/projets/paycam-mobile#devlogs',
        ])->render();

        $this->assertStringContainsString('Nouveau Devlog', $devlogHtml);
        $this->assertStringContainsString('PayCam Mobile', $devlogHtml);
        $this->assertStringContainsString('Intégration API Orange Money v2', $devlogHtml);

        // Render review email
        $reviewHtml = View::make('emails.review_notification', [
            'subscriber' => $user,
            'reviewTitle' => 'Zelda Echoes of Wisdom',
            'rubriqueName' => 'Tests Jeux Vidéo',
            'badgeText' => '🎮 TEST JEU VIDÉO',
            'categoryBadge' => 'Nintendo Switch',
            'score' => 18,
            'summary' => 'Un excellent retour aux sources avec des énigmes ingénieuses.',
            'reviewImage' => 'https://tech237.net/storage/images/zelda.jpg',
            'reviewUrl' => 'https://tech237.net/test-jeu/zelda',
        ])->render();

        $this->assertStringContainsString('Zelda Echoes of Wisdom', $reviewHtml);
        $this->assertStringContainsString('Tests Jeux Vidéo', $reviewHtml);
        $this->assertStringContainsString('18', $reviewHtml);

        // Render content notification email
        $contentHtml = View::make('emails.content_notification', [
            'subscriber' => $user,
            'articleTitle' => 'Lancement de la 5G au Cameroun',
            'targetTypeLabel' => 'Rubrique',
            'targetName' => 'Télécoms & Réseaux',
            'summary' => 'Le déploiement officiel débutera ce trimestre dans les métropoles.',
            'articleImage' => 'https://tech237.net/storage/images/5g.jpg',
            'articleUrl' => 'https://tech237.net/article/lancement-5g-cameroun',
        ])->render();

        $this->assertStringContainsString('Lancement de la 5G au Cameroun', $contentHtml);
        $this->assertStringContainsString('Télécoms &amp; Réseaux', $contentHtml);
    }

    public function test_bookmark_notification_service_handles_empty_bookmarks_gracefully()
    {
        Mail::fake();

        $project = new Project([
            'id' => 999999,
            'name' => 'Ghost Project',
            'slug' => 'ghost-project',
        ]);
        $timeline = new ProjectTimeline([
            'id' => 999999,
            'title' => 'Devlog Test',
        ]);

        BookmarkNotificationService::notifyProjectDevlog($project, $timeline);
        Mail::assertNothingSent();

        $game = new GameReview([
            'id' => 999999,
            'game_name' => 'Ghost Game',
            'slug' => 'ghost-game',
        ]);
        BookmarkNotificationService::notifyGameReview($game);
        Mail::assertNothingSent();

        $app = new AppReview([
            'id' => 999999,
            'app_name' => 'Ghost App',
            'slug' => 'ghost-app',
        ]);
        BookmarkNotificationService::notifyAppReview($app);
        Mail::assertNothingSent();

        $article = new Article([
            'id' => 999999,
            'title_fr' => 'Ghost Article',
            'slug' => 'ghost-article',
            'status' => 'draft',
        ]);
        BookmarkNotificationService::notifyArticlePublished($article);
        Mail::assertNothingSent();
    }
}
