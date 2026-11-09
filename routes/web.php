<?php

use Illuminate\Support\Facades\Route;

// Front Controllers
use App\Http\Controllers\Front\HomeController;
use App\Http\Controllers\Front\ArticleController;
use App\Http\Controllers\Front\GameReviewController;
use App\Http\Controllers\Front\AppReviewController;
use App\Http\Controllers\Front\InterviewController;
use App\Http\Controllers\Front\EntrepreneurController;
use App\Http\Controllers\Front\ForumController;
use App\Http\Controllers\Front\ConferenceController;
use App\Http\Controllers\Front\JobController;
use App\Http\Controllers\Front\StaticPageController;
use App\Http\Controllers\Front\SearchController;
use App\Http\Controllers\Front\NewsletterController;
use App\Http\Controllers\Front\LocaleController;
use App\Http\Controllers\Front\ProjectShowcaseController;
use App\Http\Controllers\Front\AuthorController;
use App\Http\Controllers\Front\CommentController;

// User Auth & Space Controllers
use App\Http\Controllers\Auth\UserAuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;

// Admin Controllers & Livewire Components
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\ImageUploadController;
use App\Livewire\Admin\Auth\Login as AdminLogin;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\Articles\Index as AdminArticlesIndex;
use App\Livewire\Admin\Articles\Form as AdminArticlesForm;
use App\Livewire\Admin\Dossiers\Index as AdminDossiersIndex;
use App\Livewire\Admin\Rubriques\Index as AdminRubriquesIndex;
use App\Livewire\Admin\Categories\Index as AdminCategoriesIndex;
use App\Livewire\Admin\Tags\Index as AdminTagsIndex;
use App\Livewire\Admin\Entrepreneurs\Index as AdminEntrepreneursIndex;
use App\Livewire\Admin\Entrepreneurs\Form as AdminEntrepreneursForm;
use App\Livewire\Admin\Forum\Index as AdminForumIndex;
use App\Livewire\Admin\Conferences\Index as AdminConferencesIndex;
use App\Livewire\Admin\Jobs\Index as AdminJobsIndex;
use App\Livewire\Admin\AppReviews\Index as AdminAppReviewsIndex;
use App\Livewire\Admin\AppReviews\Form as AdminAppReviewsForm;
use App\Livewire\Admin\GameReviews\Index as AdminGameReviewsIndex;
use App\Livewire\Admin\GameReviews\Form as AdminGameReviewsForm;
use App\Livewire\Admin\Interviews\Index as AdminInterviewsIndex;
use App\Livewire\Admin\Interviews\Form as AdminInterviewsForm;
use App\Livewire\Admin\Sliders\Index as AdminSlidersIndex;
use App\Livewire\Admin\Banners\Index as AdminBannersIndex;
use App\Livewire\Admin\Newsletters\Index as AdminNewslettersIndex;
use App\Livewire\Admin\Analytics\Index as AdminAnalyticsIndex;
use App\Livewire\Admin\Startups\Index as AdminStartupsIndex;
use App\Livewire\Admin\FrontUsers\Index as AdminFrontUsersIndex;
use App\Livewire\Admin\Disputes\Index as AdminDisputesIndex;
use App\Livewire\Admin\Comments\Index as AdminCommentsIndex;
use App\Livewire\Admin\Users\Index as AdminUsersIndex;
use App\Livewire\Admin\Banned\Index as AdminBannedIndex;
use App\Livewire\Admin\Logs\Index as AdminLogsIndex;
use App\Livewire\Admin\Stats\MyStats as AdminMyStats;
use App\Livewire\Admin\Stats\WritersOverview as AdminWritersOverview;
use App\Livewire\Admin\Stats\WriterDetail as AdminWriterDetail;
use App\Livewire\Admin\Settings\Index as AdminSettingsIndex;
use App\Livewire\Admin\Profile\Index as AdminProfileIndex;

/*
|--------------------------------------------------------------------------
| Public Front-End Routes (Inertia.js + Vue.js 3 - Single Page App Feel)
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/article/{slug}', [ArticleController::class, 'show'])->name('article.show');
Route::get('/rubrique/{slug}', [ArticleController::class, 'byRubrique'])->name('rubrique.show');
Route::get('/category/{slug}', [ArticleController::class, 'byCategory'])->name('category.show');
Route::get('/dossier/{slug}', [ArticleController::class, 'byDossier'])->name('dossier.show');
Route::get('/index-dossiers', [ArticleController::class, 'dossiersIndex'])->name('dossiers.public_index');

// Entrepreneur du mois
Route::get('/entrepreneur-du-mois', [EntrepreneurController::class, 'showCurrent'])->name('entrepreneurs.current');
Route::get('/entrepreneur-du-mois/{id}', [EntrepreneurController::class, 'show'])->name('entrepreneurs.show');
Route::get('/entrepreneur', [EntrepreneurController::class, 'showCurrent']);
Route::get('/entrepreneurs', [EntrepreneurController::class, 'showCurrent']);

// Forum Module
Route::get('/forum', [ForumController::class, 'index'])->name('forum.index');
Route::get('/forum/auteur/{userId}', [ForumController::class, 'userTopics'])->name('forum.author');
Route::get('/forum/{categorySlug}/{topicSlug}', [ForumController::class, 'show'])->name('forum.show');
Route::post('/forum/creer-sujet', [ForumController::class, 'storeTopic'])->name('forum.topics.store')->middleware('auth:web');
Route::post('/forum/repondre/{topicId}', [ForumController::class, 'reply'])->name('forum.posts.store')->middleware('auth:web');
Route::post('/forum/topic/{topicId}/resoudre', [ForumController::class, 'toggleSolve'])->name('forum.topics.solve')->middleware('auth:web');
Route::post('/forum/post/{postId}/like', [ForumController::class, 'toggleLike'])->name('forum.posts.like')->middleware('auth:web');
Route::post('/forum/signaler', [ForumController::class, 'report'])->name('forum.report');

// Conférences & Événements
Route::get('/conferences', [ConferenceController::class, 'index'])->name('conferences.index');
Route::get('/conference/{slug}', [ConferenceController::class, 'show'])->name('conferences.show');
Route::post('/conference/{id}/inscription', [ConferenceController::class, 'register'])->name('conferences.register');
Route::post('/conference/proposer', [ConferenceController::class, 'propose'])->name('conferences.propose')->middleware('auth:web');

// Offres d'Emploi IT
Route::get('/emplois', [JobController::class, 'index'])->name('jobs.index');
Route::get('/emploi/{slug}', [JobController::class, 'show'])->name('jobs.show');
Route::get('/emploi/{slug}/postuler', [JobController::class, 'applyForm'])->name('jobs.apply.form');
Route::post('/emploi/{id}/postuler', [JobController::class, 'apply'])->name('jobs.apply');
Route::get('/api/jobs/{id}/check-email', [JobController::class, 'checkAppliedEmail'])->name('api.jobs.check-email');

// Reviews & Interviews
Route::get('/tests-jeux-video', [GameReviewController::class, 'index'])->name('games.index');
Route::get('/test-jeu/{slug}', [GameReviewController::class, 'show'])->name('games.show');
Route::get('/tests-applications', [AppReviewController::class, 'index'])->name('apps.index');
Route::get('/test-app/{slug}', [AppReviewController::class, 'show'])->name('apps.show');
Route::get('/interviews', [InterviewController::class, 'index'])->name('interviews.index');
Route::get('/interview/{slug}', [InterviewController::class, 'show'])->name('interviews.show');

// Public Showcase for Projects & Startups (Investors & Community)
Route::get('/projets', [ProjectShowcaseController::class, 'index'])->name('projects.index');
Route::get('/projet/{slug}', [ProjectShowcaseController::class, 'show'])->name('projects.show');
Route::get('/startups', [ProjectShowcaseController::class, 'startupsIndex'])->name('startups.index');
Route::get('/startups/{slug}', [ProjectShowcaseController::class, 'showStartup'])->name('startups.show');
Route::get('/startup/{slug}', [ProjectShowcaseController::class, 'showStartup']);
Route::post('/startups/{id}/contact', [ProjectShowcaseController::class, 'contactStartup'])->name('startups.contact');
Route::post('/api/report', [ProjectShowcaseController::class, 'report'])->name('api.report');

// Project Collaboration Invitations (Public Token Access & Actions)
Route::get('/invitations/{token}', [App\Http\Controllers\User\ProjectCollaborationController::class, 'showInvitation'])->name('invitations.show');
Route::post('/invitations/{token}/accepter', [App\Http\Controllers\User\ProjectCollaborationController::class, 'acceptInvitation'])->name('invitations.accept');
Route::post('/invitations/{token}/refuser', [App\Http\Controllers\User\ProjectCollaborationController::class, 'declineInvitation'])->name('invitations.decline');
Route::post('/invitations/{token}/bloquer', [App\Http\Controllers\User\ProjectCollaborationController::class, 'blockCollaboration'])->name('invitations.block');

// Public Author Profile
Route::get('/auteur/{id}', [AuthorController::class, 'show'])->name('authors.show');

// Comments Submission
Route::post('/api/comments', [CommentController::class, 'store'])->name('api.comments.store')->middleware('auth:web');

// Static Pages (Support, Terms, Privacy)
Route::get('/nous-soutenir', [StaticPageController::class, 'support'])->name('support');
Route::get('/conditions-generales', [StaticPageController::class, 'terms'])->name('terms');
Route::get('/politique-de-confidentialite', [StaticPageController::class, 'privacy'])->name('privacy');

// Utilities (Locale switch, Live Autocompletion, Newsletter Subscription, WYSIWYG Upload)
Route::post('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');
Route::get('/api/search-autocomplete', [SearchController::class, 'autocomplete'])->name('api.search');
Route::post('/api/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('api.newsletter');
Route::post('/api/wysiwyg-upload', [ImageUploadController::class, 'upload'])->name('wysiwyg.upload');

/*
|--------------------------------------------------------------------------
| User Authentication & Dashboard (Inertia + Vue 3)
|--------------------------------------------------------------------------
*/
Route::middleware('guest:web')->group(function () {
    Route::get('/register', [UserAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [UserAuthController::class, 'register']);
    Route::get('/login', [UserAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [UserAuthController::class, 'login']);

    // Forgot & Reset Password
    Route::get('/mot-de-passe-oublie', [PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/mot-de-passe-oublie', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reinitialiser-mot-de-passe/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reinitialiser-mot-de-passe', [PasswordResetController::class, 'reset'])->name('password.update');
});

Route::middleware('auth:web')->group(function () {
    Route::post('/logout', [UserAuthController::class, 'logout'])->name('logout');
    Route::get('/espace-utilisateur', [UserDashboardController::class, 'index'])->name('user.dashboard');
    
    // Quick section aliases
    Route::get('/espace-utilisateur/projets', function () {
        return redirect()->route('user.dashboard', ['section' => 'projects']);
    })->name('user.projects.index');
    Route::get('/espace-utilisateur/startups', function () {
        return redirect()->route('user.dashboard', ['section' => 'startups']);
    })->name('user.startups.index');
    Route::get('/espace-utilisateur/jobs', function () {
        return redirect()->route('user.dashboard', ['section' => 'jobs']);
    })->name('user.jobs.index');
    Route::get('/espace-utilisateur/conferences', function () {
        return redirect()->route('user.dashboard', ['section' => 'conferences']);
    })->name('user.conferences.index');
    
    // Startups dedicated pages
    Route::get('/espace-utilisateur/startups/create', [UserDashboardController::class, 'createStartup'])->name('user.startups.create');
    Route::post('/espace-utilisateur/startup', [UserDashboardController::class, 'storeStartup'])->name('user.startups.store');
    Route::post('/espace-utilisateur/startups', [UserDashboardController::class, 'storeStartup']);
    Route::get('/espace-utilisateur/startups/{id}/edit', [UserDashboardController::class, 'editStartup'])->name('user.startups.edit');
    Route::post('/espace-utilisateur/startups/{id}', [UserDashboardController::class, 'updateStartup'])->name('user.startups.update');
    
    // Projects dedicated pages
    Route::get('/espace-utilisateur/projets/create', [UserDashboardController::class, 'createProject'])->name('user.projects.create');
    Route::post('/espace-utilisateur/projet', [UserDashboardController::class, 'storeProject'])->name('user.projects.store');
    Route::post('/espace-utilisateur/projets', [UserDashboardController::class, 'storeProject']);
    Route::get('/espace-utilisateur/projets/{id}/edit', [UserDashboardController::class, 'editProject'])->name('user.projects.edit');
    Route::post('/espace-utilisateur/projets/{id}', [UserDashboardController::class, 'updateProject'])->name('user.projects.update');
    Route::get('/espace-utilisateur/projets/{id}/etapes', [UserDashboardController::class, 'projectTimeline'])->name('user.projects.timeline');
    Route::post('/espace-utilisateur/projet/{id}/timeline', [UserDashboardController::class, 'storeTimeline'])->name('user.timelines.store');
    Route::post('/espace-utilisateur/projets/{id}/timeline', [UserDashboardController::class, 'storeTimeline']);
    
    // Project Images Dropzone Gallery
    Route::get('/espace-utilisateur/projets/{id}/galerie', [UserDashboardController::class, 'projectGallery'])->name('user.projects.gallery');
    Route::post('/espace-utilisateur/projets/{id}/galerie', [UserDashboardController::class, 'uploadProjectImages'])->name('user.projects.gallery.upload');
    Route::delete('/espace-utilisateur/projets/images/{id}', [UserDashboardController::class, 'deleteProjectImage'])->name('user.projects.gallery.delete');

    // Project Collaboration Management
    Route::get('/espace-utilisateur/projets/{id}/collaborateurs', [App\Http\Controllers\User\ProjectCollaborationController::class, 'manageCollaborators'])->name('user.projects.collaborators');
    Route::get('/api/users/search', [App\Http\Controllers\User\ProjectCollaborationController::class, 'searchUsers'])->name('api.users.search');
    Route::get('/espace-utilisateur/projets/collaborateurs/search-users', [App\Http\Controllers\User\ProjectCollaborationController::class, 'searchUsers'])->name('user.projects.collaborators.search');
    Route::post('/espace-utilisateur/projets/{id}/inviter', [App\Http\Controllers\User\ProjectCollaborationController::class, 'invite'])->name('user.projects.invite')->middleware('throttle:15,1');
    Route::delete('/espace-utilisateur/projets/{projectId}/collaborateurs/{collaboratorId}', [App\Http\Controllers\User\ProjectCollaborationController::class, 'revokeCollaborator'])->name('user.projects.collaborators.revoke');
    Route::post('/espace-utilisateur/timelines/{timelineId}/valider', [App\Http\Controllers\User\ProjectCollaborationController::class, 'validateTimeline'])->name('user.timelines.validate');
    Route::post('/espace-utilisateur/images/{imageId}/valider', [App\Http\Controllers\User\ProjectCollaborationController::class, 'validateImage'])->name('user.images.validate');
    
    // User Jobs Management
    Route::get('/espace-utilisateur/jobs/create', [UserDashboardController::class, 'createJobOffer'])->name('user.jobs.create');
    Route::post('/espace-utilisateur/jobs', [UserDashboardController::class, 'storeJobOffer'])->name('user.jobs.store');
    Route::get('/espace-utilisateur/jobs/{id}/edit', [UserDashboardController::class, 'editJobOffer'])->name('user.jobs.edit');
    Route::post('/espace-utilisateur/jobs/{id}', [UserDashboardController::class, 'updateJobOffer'])->name('user.jobs.update');
    Route::get('/espace-utilisateur/jobs/{id}/questions', [UserDashboardController::class, 'jobQuestions'])->name('user.jobs.questions');
    Route::post('/espace-utilisateur/jobs/{id}/questions', [UserDashboardController::class, 'saveJobQuestions'])->name('user.jobs.questions.save');
    Route::get('/espace-utilisateur/jobs/{id}/candidats', [UserDashboardController::class, 'jobApplicants'])->name('user.jobs.applicants');
    Route::post('/espace-utilisateur/jobs/candidats/{applicationId}/examiner', [UserDashboardController::class, 'examineTest'])->name('user.jobs.examine');
    Route::post('/espace-utilisateur/jobs/candidats/{applicationId}/convoquer', [UserDashboardController::class, 'scheduleInterview'])->name('user.jobs.schedule');
    Route::post('/espace-utilisateur/jobs/candidats/{applicationId}/rejeter', [UserDashboardController::class, 'rejectApplicant'])->name('user.jobs.reject');

    // User Conferences & Events Management
    Route::get('/espace-utilisateur/conferences/create', [UserDashboardController::class, 'createConference'])->name('user.conferences.create');
    Route::post('/espace-utilisateur/conferences', [UserDashboardController::class, 'storeConference'])->name('user.conferences.store');
    Route::post('/espace-utilisateur/conference', [UserDashboardController::class, 'storeConference']);
    Route::get('/espace-utilisateur/conferences/{id}/edit', [UserDashboardController::class, 'editConference'])->name('user.conferences.edit');
    Route::post('/espace-utilisateur/conferences/{id}', [UserDashboardController::class, 'updateConference'])->name('user.conferences.update');
    Route::get('/espace-utilisateur/conferences/{id}/participants', [UserDashboardController::class, 'conferenceParticipants'])->name('user.conferences.participants');
    Route::post('/espace-utilisateur/conferences/participants/{regId}/confirmer', [UserDashboardController::class, 'sendConfirmationEmail'])->name('user.conferences.confirm_one');
    Route::post('/espace-utilisateur/conferences/{id}/participants/confirmer-tous', [UserDashboardController::class, 'sendBulkConfirmationEmails'])->name('user.conferences.confirm_bulk');

    // Profile, messages and documents
    Route::post('/espace-utilisateur/profil', [UserDashboardController::class, 'updateProfile'])->name('user.profile.update');
    Route::post('/espace-utilisateur/messages/envoyer', [UserDashboardController::class, 'sendMessage'])->name('user.messages.send');
    Route::post('/espace-utilisateur/messages/{id}/read', [UserDashboardController::class, 'markMessageRead'])->name('user.messages.read');
    Route::delete('/espace-utilisateur/documents/{id}', [UserDashboardController::class, 'deleteDocument'])->name('user.documents.delete');
});

/*
|--------------------------------------------------------------------------
| Administration Routes (Laravel Livewire + Bootstrap 5)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    // Guest Admin Login
    Route::get('/login', AdminLogin::class)->name('login');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    // Authenticated Admin Section
    Route::middleware(['admin.auth', 'admin.log'])->group(function () {
        Route::get('/', AdminDashboard::class)->name('dashboard');

        // Toggle Snowfall Effect
        Route::post('/toggle-snow', function () {
            $settings = \App\Models\SiteSetting::current();
            $settings->update(['snow_effect_enabled' => !$settings->snow_effect_enabled]);
            $status = $settings->fresh()->snow_effect_enabled ? 'activée' : 'désactivée';
            \App\Models\AdminLog::record('settings_snow_toggled', "Effet de chute de neige {$status} sur le site");
            return back()->with('success', "L'effet de chute de neige a été {$status} avec succès sur le site !");
        })->name('toggle-snow');

        // Articles (WYSIWYG FR/EN, storage/images/, validation review)
        Route::get('/articles', AdminArticlesIndex::class)->name('articles.index');
        Route::get('/articles/create', AdminArticlesForm::class)->name('articles.create');
        Route::get('/articles/{id}/edit', AdminArticlesForm::class)->name('articles.edit');
        Route::post('/images/upload', [ImageUploadController::class, 'upload'])->name('images.upload');

        // Content Organization (Dossiers, Rubriques, Categories, Tags)
        Route::get('/dossiers', AdminDossiersIndex::class)->name('dossiers.index');
        Route::get('/rubriques', AdminRubriquesIndex::class)->name('rubriques.index');
        Route::get('/categories', AdminCategoriesIndex::class)->name('categories.index');
        Route::get('/tags', AdminTagsIndex::class)->name('tags.index');

        // Entrepreneur du mois
        Route::get('/entrepreneurs', AdminEntrepreneursIndex::class)->name('entrepreneurs.index');
        Route::get('/entrepreneurs/create', AdminEntrepreneursForm::class)->name('entrepreneurs.create');
        Route::get('/entrepreneurs/{id}/edit', AdminEntrepreneursForm::class)->name('entrepreneurs.edit');

        // Conférences & Événements
        Route::get('/conferences', AdminConferencesIndex::class)->name('conferences.index');

        // Offres d'Emploi IT
        Route::get('/jobs', AdminJobsIndex::class)->name('jobs.index');

        // Forum Moderation
        Route::get('/forum', AdminForumIndex::class)->name('forum.index');

        // App Reviews (Notes UI, UX, Prise en main, Stores)
        Route::get('/app-reviews', AdminAppReviewsIndex::class)->name('app-reviews.index');
        Route::get('/app-reviews/create', AdminAppReviewsForm::class)->name('app-reviews.create');
        Route::get('/app-reviews/{id}/edit', AdminAppReviewsForm::class)->name('app-reviews.edit');

        // Video Game Reviews (Gameplay, Graphismes, Access., Audio, Consoles, Pros/Cons)
        Route::get('/game-reviews', AdminGameReviewsIndex::class)->name('game-reviews.index');
        Route::get('/game-reviews/create', AdminGameReviewsForm::class)->name('game-reviews.create');
        Route::get('/game-reviews/{id}/edit', AdminGameReviewsForm::class)->name('game-reviews.edit');

        // Interviews (YouTube embed, WYSIWYG FR/EN)
        Route::get('/interviews', AdminInterviewsIndex::class)->name('interviews.index');
        Route::get('/interviews/create', AdminInterviewsForm::class)->name('interviews.create');
        Route::get('/interviews/{id}/edit', AdminInterviewsForm::class)->name('interviews.edit');

        // Marketing & Communication
        Route::get('/sliders', AdminSlidersIndex::class)->name('sliders.index');
        Route::get('/banners', AdminBannersIndex::class)->name('banners.index');
        Route::get('/newsletters', AdminNewslettersIndex::class)->name('newsletters.index');

        // Visitor Analytics (Logs, Graphiques, Sessions, Géoloc)
        Route::get('/analytics', AdminAnalyticsIndex::class)->name('analytics.index');

        // Statistiques & Rémunération Rédacteurs (Lectures uniques)
        Route::get('/mes-statistiques', AdminMyStats::class)->name('my-stats');
        Route::get('/statistiques-redacteurs', AdminWritersOverview::class)->name('writers.overview');
        Route::get('/statistiques-redacteurs/{writerId}', AdminWriterDetail::class)->name('writers.detail');

        // Startups & Cameroon Legal Validation
        Route::get('/startups', AdminStartupsIndex::class)->name('startups.index');

        // Front-end Users & Ban
        Route::get('/front-users', AdminFrontUsersIndex::class)->name('front-users.index');

        // Content Moderation, Comments & Disputes
        Route::get('/comments', AdminCommentsIndex::class)->name('comments.index');
        Route::get('/disputes', AdminDisputesIndex::class)->name('disputes.index');

        // Administration, Security & Users
        Route::get('/users', AdminUsersIndex::class)->name('users.index');
        Route::get('/logs', AdminLogsIndex::class)->name('logs.index');
        Route::get('/settings', AdminSettingsIndex::class)->name('settings.index');
        Route::get('/profile', AdminProfileIndex::class)->name('profile.index');

        // Super Admin Exclusive Menu for Banned Admins
        Route::get('/banned-admins', AdminBannedIndex::class)->name('banned.index')->middleware('admin.super');
    });
});

Route::match(['get', 'head'], 'livewire-8067d3f9/update', fn() => redirect()->back());
Route::match(['get', 'head'], 'livewire/update', fn() => redirect()->back());

Route::get('/create-symlink', function () {
    if (is_link(public_path('storage'))) {
        return 'Le lien symbolique existe déjà !';
    }

    try {
        symlink(
            storage_path('app/public'),
            public_path('storage')
        );
        return 'Lien symbolique créé avec succès !';
    } catch (\Exception $e) {
        return 'Erreur : ' . $e->getMessage();
    }
});
