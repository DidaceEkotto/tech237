<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Startup;
use App\Models\Project;
use App\Models\ProjectTimeline;
use App\Models\ProjectImage;
use App\Models\ProjectInvitation;
use App\Models\ProjectCollaborator;
use App\Models\CollaborationBlock;
use App\Models\ProgrammingLanguage;
use App\Models\Framework;
use App\Models\UserDocument;
use App\Models\UserMessage;
use App\Models\Comment;
use App\Models\JobOffer;
use App\Models\JobQuestion;
use App\Models\JobApplication;
use App\Models\JobInterview;
use App\Models\Conference;
use App\Models\ConferenceRegistration;
use App\Models\SiteSetting;
use App\Models\UserBookmark;
use App\Models\UserBadge;
use App\Models\UserAlertPreference;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Services\ImageService;
use Inertia\Inertia;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::guard('web')->user();

        $startups = Startup::where('user_id', $user->id)
            ->with(['documents', 'projects.timelines'])
            ->latest()
            ->get();

        $projects = Project::where('user_id', $user->id)
            ->with([
                'startup',
                'timelines' => fn($q) => $q->with('user')->orderBy('event_date', 'desc'),
                'activeCollaborators.user',
                'invitations' => fn($q) => $q->where('status', 'pending')->where('expires_at', '>', now()),
            ])
            ->latest()
            ->get();

        // Collaborative projects where user was invited and accepted
        $collaborativeProjects = Project::whereHas('activeCollaborators', fn($q) => $q->where('user_id', $user->id))
            ->with([
                'user',
                'startup',
                'timelines' => fn($q) => $q->where('status', 'approved')->orderBy('event_date', 'desc'),
                'activeCollaborators.user',
            ])
            ->latest()
            ->get();

        // Pending collaboration invitations received by this user
        $pendingInvitations = ProjectInvitation::with(['project.user', 'project.startup', 'inviter'])
            ->where(function($q) use ($user) {
                $q->where('invited_user_id', $user->id)
                  ->orWhere('email', strtolower(trim($user->email)));
            })
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->latest()
            ->get();

        // Pending validations for owned projects (Devlogs & Images submitted by collaborators)
        $ownedProjectIds = $projects->pluck('id');
        $pendingTimelines = ProjectTimeline::whereIn('project_id', $ownedProjectIds)
            ->where('status', 'pending')
            ->with(['project', 'user'])
            ->latest()
            ->get();

        $pendingImages = ProjectImage::whereIn('project_id', $ownedProjectIds)
            ->where('status', 'pending')
            ->with(['project', 'user'])
            ->latest()
            ->get();

        $jobOffers = JobOffer::where('user_id', $user->id)
            ->withCount('applications')
            ->with('questions')
            ->latest()
            ->get();

        $conferences = Conference::where('user_id', $user->id)
            ->withCount('registrations')
            ->latest('event_date')
            ->get();

        $documents = UserDocument::where('user_id', $user->id)->latest()->get();

        $messages = UserMessage::where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('sender_id', $user->id);
            })
            ->with(['admin', 'sender', 'user'])
            ->latest()
            ->get();

        $unreadMessagesCount = UserMessage::where('user_id', $user->id)->where('is_read', false)->count();

        // User comments and replies received
        $comments = Comment::where('user_id', $user->id)
            ->with(['article', 'interview', 'project', 'replies.user'])
            ->latest()
            ->get();

        $unreadCommentsCount = Comment::whereHas('parent', fn($q) => $q->where('user_id', $user->id))
            ->where('user_id', '!=', $user->id)
            ->count();

        $totalProjectViews = $projects->sum('views_count');
        $totalProjectComments = Comment::whereIn('project_id', $projects->pluck('id'))->count();

        // Bookmarks (Reading List)
        $rawBookmarks = UserBookmark::where('user_id', $user->id)->latest()->get();
        $bookmarks = [];
        foreach ($rawBookmarks as $b) {
            $item = $b->target_item;
            if ($item) {
                $title = $item->title_fr ?? $item->name ?? $item->game_name ?? $item->app_name ?? 'Élément';
                $slug = $item->slug ?? null;
                $url = match ($b->bookmarkable_type) {
                    'article' => route('article.show', $slug ?: $item->id),
                    'game_review' => route('game-reviews.show', $slug ?: $item->id),
                    'app_review' => route('app-reviews.show', $slug ?: $item->id),
                    'project' => route('projects.show', $slug ?: $item->id),
                    'job' => route('jobs.show', $slug ?: $item->id),
                    default => '#',
                };
                $image = $item->image_path ?? $item->image ?? $item->logo ?? $item->featured_image ?? null;
                $bookmarks[] = [
                    'id' => $b->id,
                    'type' => $b->bookmarkable_type,
                    'type_label' => match($b->bookmarkable_type) {
                        'article' => 'Article',
                        'game_review' => 'Test Jeu',
                        'app_review' => 'Test App',
                        'project' => 'Projet Tech',
                        'job' => 'Emploi IT',
                        default => 'Autre',
                    },
                    'item_id' => $b->bookmarkable_id,
                    'title' => $title,
                    'url' => $url,
                    'image' => $image,
                    'created_at' => $b->created_at->format('d/m/Y'),
                ];
            }
        }

        $badges = UserBadge::where('user_id', $user->id)->latest()->get();
        $alertPreference = UserAlertPreference::firstOrCreate(
            ['user_id' => $user->id],
            [
                'alert_jobs' => true,
                'alert_conferences' => true,
                'alert_projects' => true,
                'alert_newsletter' => true,
            ]
        );

        // Analytics curves for Day, Week, Month
        $stats = [
            'project_views' => $totalProjectViews,
            'project_comments' => $totalProjectComments,
            'total_projects' => $projects->count(),
            'total_startups' => $startups->count(),
            'charts' => [
                'day' => [
                    'labels' => ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
                    'views' => [18, 25, 42, 35, 58, 72, 65],
                    'interactions' => [2, 5, 8, 4, 11, 14, 9],
                ],
                'week' => [
                    'labels' => ['Semaine 1', 'Semaine 2', 'Semaine 3', 'Semaine 4'],
                    'views' => [160, 240, 210, 320],
                    'interactions' => [28, 45, 38, 62],
                ],
                'month' => [
                    'labels' => ['Juin', 'Juil', 'Août', 'Sept', 'Oct', 'Nov'],
                    'views' => [620, 890, 1050, 1340, 1680, 2100],
                    'interactions' => [95, 140, 185, 230, 290, 380],
                ],
            ]
        ];

        return Inertia::render('User/Dashboard', [
            'user' => $user,
            'startups' => $startups,
            'projects' => $projects,
            'collaborativeProjects' => $collaborativeProjects,
            'pendingInvitations' => $pendingInvitations,
            'pendingTimelines' => $pendingTimelines,
            'pendingImages' => $pendingImages,
            'jobOffers' => $jobOffers,
            'conferences' => $conferences,
            'documents' => $documents,
            'messages' => $messages,
            'unreadMessagesCount' => $unreadMessagesCount,
            'comments' => $comments,
            'unreadCommentsCount' => $unreadCommentsCount,
            'bookmarks' => $bookmarks,
            'badges' => $badges,
            'alertPreference' => $alertPreference,
            'stats' => $stats,
            'isBanned' => $user->isBanned(),
            'banReason' => $user->ban_reason,
            'activeSection' => $request->query('section', 'dashboard'),
        ]);
    }

    public function createStartup()
    {
        return Inertia::render('User/Startups/Create');
    }

    public function editStartup(int $id)
    {
        $user = Auth::guard('web')->user();
        $startup = Startup::where('user_id', $user->id)->with('documents')->findOrFail($id);

        return Inertia::render('User/Startups/Edit', [
            'startup' => $startup,
        ]);
    }

    public function storeStartup(Request $request)
    {
        $user = Auth::guard('web')->user();
        if ($user->isBanned()) {
            return back()->withErrors(['error' => 'Votre compte est banni. Impossible d\'ajouter une startup.']);
        }

        $request->validate([
            'name' => 'required|string|max:150',
            'legal_type' => 'required|string|in:SARL,SA,SAS,EURL,ETS,GIE',
            'city' => 'required|string|max:100',
            'location' => 'nullable|string|max:200',
            'employee_count' => 'required|integer|min:1',
            'needs_funding' => 'boolean',
            'funding_amount' => 'nullable|numeric|min:0',
            'rccm_number' => 'nullable|string|max:100',
            'niu_number' => 'nullable|string|max:100',
            'description' => 'required|string',
            'logo' => 'required|image|max:5120',
            'banner' => 'nullable|image|max:10240',
            'cni_recto' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:10240',
            'cni_verso' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:10240',
            'rccm_doc' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:10240',
        ], [
            'logo.required' => 'Le logo officiel de la startup est OBLIGATOIRE pour la validation de votre dossier.',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = ImageService::saveImage($request->file('logo'), 'startup_logo');
        }

        $bannerPath = null;
        if ($request->hasFile('banner')) {
            $bannerPath = ImageService::saveImage($request->file('banner'), 'startup_banner');
        }

        $startup = Startup::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . Str::random(4),
            'logo' => $logoPath,
            'banner' => $bannerPath,
            'legal_type' => $request->legal_type,
            'city' => $request->city,
            'location' => $request->location,
            'employee_count' => $request->employee_count,
            'needs_funding' => $request->boolean('needs_funding'),
            'funding_amount' => $request->funding_amount,
            'rccm_number' => $request->rccm_number,
            'niu_number' => $request->niu_number,
            'description' => $request->description,
            'status' => 'pending_validation',
        ]);

        if ($request->hasFile('cni_recto')) {
            $docPath = ImageService::saveImage($request->file('cni_recto'), 'cni_recto');
            UserDocument::create([
                'user_id' => $user->id,
                'startup_id' => $startup->id,
                'type' => 'cni_recto',
                'file_path' => $docPath,
                'title' => 'Carte Nationale d\'Identité (Recto)',
            ]);
        }

        if ($request->hasFile('cni_verso')) {
            $docPath = ImageService::saveImage($request->file('cni_verso'), 'cni_verso');
            UserDocument::create([
                'user_id' => $user->id,
                'startup_id' => $startup->id,
                'type' => 'cni_verso',
                'file_path' => $docPath,
                'title' => 'Carte Nationale d\'Identité (Verso)',
            ]);
        }

        if ($request->hasFile('rccm_doc')) {
            $docPath = ImageService::saveImage($request->file('rccm_doc'), 'rccm_cert');
            UserDocument::create([
                'user_id' => $user->id,
                'startup_id' => $startup->id,
                'type' => 'rccm_cert',
                'file_path' => $docPath,
                'title' => 'Extrait RCCM / Statuts OHADA',
            ]);
        }

        return redirect('/espace-utilisateur?section=startups')->with('success', 'Startup soumise avec succès ! Examen en cours sous 2 jours ouvrés.');
    }

    public function updateStartup(Request $request, int $id)
    {
        $user = Auth::guard('web')->user();
        $startup = Startup::where('user_id', $user->id)->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:150',
            'legal_type' => 'required|string|in:SARL,SA,SAS,EURL,ETS,GIE',
            'city' => 'required|string|max:100',
            'location' => 'nullable|string|max:200',
            'employee_count' => 'required|integer|min:1',
            'needs_funding' => 'boolean',
            'funding_amount' => 'nullable|numeric|min:0',
            'rccm_number' => 'nullable|string|max:100',
            'niu_number' => 'nullable|string|max:100',
            'description' => 'required|string',
            'logo' => 'nullable|image|max:5120',
            'banner' => 'nullable|image|max:10240',
        ]);

        $data = [
            'name' => $request->name,
            'legal_type' => $request->legal_type,
            'city' => $request->city,
            'location' => $request->location,
            'employee_count' => $request->employee_count,
            'needs_funding' => $request->boolean('needs_funding'),
            'funding_amount' => $request->funding_amount,
            'rccm_number' => $request->rccm_number,
            'niu_number' => $request->niu_number,
            'description' => $request->description,
        ];

        if ($request->hasFile('logo')) {
            $data['logo'] = ImageService::saveImage($request->file('logo'), 'startup_logo');
        }
        if ($request->hasFile('banner')) {
            $data['banner'] = ImageService::saveImage($request->file('banner'), 'startup_banner');
        }

        $startup->update($data);

        return redirect('/espace-utilisateur?section=startups')->with('success', 'Startup mise à jour avec succès.');
    }

    public function createProject()
    {
        $user = Auth::guard('web')->user();
        $startups = Startup::where('user_id', $user->id)->get();
        $programmingLanguages = ProgrammingLanguage::with('frameworks')->orderBy('name')->get();
        $frameworks = Framework::orderBy('name')->get();

        return Inertia::render('User/Projects/Create', [
            'startups' => $startups,
            'programmingLanguages' => $programmingLanguages,
            'frameworks' => $frameworks,
        ]);
    }

    public function editProject(int $id)
    {
        $user = Auth::guard('web')->user();
        $project = Project::where('user_id', $user->id)
            ->with(['programmingLanguages', 'frameworksList', 'images'])
            ->findOrFail($id);
        $startups = Startup::where('user_id', $user->id)->get();
        $programmingLanguages = ProgrammingLanguage::with('frameworks')->orderBy('name')->get();
        $frameworks = Framework::orderBy('name')->get();

        return Inertia::render('User/Projects/Edit', [
            'project' => $project,
            'startups' => $startups,
            'programmingLanguages' => $programmingLanguages,
            'frameworks' => $frameworks,
        ]);
    }

    public function storeProject(Request $request)
    {
        $user = Auth::guard('web')->user();
        if ($user->isBanned()) {
            return back()->withErrors(['error' => 'Votre compte est banni. Impossible d\'ajouter un projet.']);
        }

        $request->validate([
            'name' => 'required|string|max:150',
            'startup_id' => 'nullable|exists:startups,id',
            'project_type' => 'required|string|in:web,mobile,desktop,game',
            'app_types' => 'nullable|array',
            'game_types' => 'nullable|array',
            'features' => 'nullable|array',
            'youtube_presentation_url' => 'nullable|url',
            'appgallery_url' => 'nullable|url',
            'development_duration' => 'nullable|string|max:100',
            'description' => 'required|string',
            'website_url' => 'nullable|url',
            'playstore_url' => 'nullable|url',
            'appstore_url' => 'nullable|url',
            'repo_url' => 'nullable|url',
            'logo' => 'nullable|image|max:5120',
            'banner' => 'nullable|image|max:10240',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = ImageService::saveImage($request->file('logo'), 'proj_logo');
        }

        $bannerPath = null;
        if ($request->hasFile('banner')) {
            $bannerPath = ImageService::saveImage($request->file('banner'), 'proj_banner');
        }

        $features = $request->input('features', []);
        if (empty($features)) {
            $features = ['Fonctionnalité principale'];
        }

        $project = Project::create([
            'user_id' => $user->id,
            'startup_id' => $request->startup_id ?: null,
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . Str::random(4),
            'logo' => $logoPath,
            'banner' => $bannerPath,
            'project_type' => $request->project_type,
            'app_types' => $request->app_types ?: [],
            'game_types' => $request->game_types ?: [],
            'features' => $features,
            'youtube_presentation_url' => $request->youtube_presentation_url ?: null,
            'appgallery_url' => $request->appgallery_url ?: null,
            'development_duration' => $request->development_duration,
            'description' => $request->description,
            'website_url' => $request->website_url,
            'playstore_url' => $request->playstore_url,
            'appstore_url' => $request->appstore_url,
            'repo_url' => $request->repo_url,
            'status' => 'published',
        ]);

        // Sync or create Programming Languages
        $langIds = $request->input('language_ids', []);
        $langNames = [];
        if (!empty($langIds)) {
            $langNames = ProgrammingLanguage::whereIn('id', $langIds)->pluck('name')->toArray();
        }
        if ($request->filled('custom_languages')) {
            $customLangs = array_map('trim', explode(',', $request->custom_languages));
            foreach ($customLangs as $langName) {
                if ($langName) {
                    $langObj = ProgrammingLanguage::firstOrCreate(
                        ['name' => $langName],
                        ['slug' => Str::slug($langName), 'color' => '#00A859']
                    );
                    $langIds[] = $langObj->id;
                    $langNames[] = $langName;
                }
            }
        }
        $langNames = array_values(array_unique(array_filter($langNames)));
        $project->update(['languages' => $langNames]);
        if (!empty($langIds) || $request->has('language_ids')) {
            $project->programmingLanguages()->sync(array_unique(array_filter($langIds)));
        }

        // Sync or create Frameworks
        $fwIds = $request->input('framework_ids', []);
        $fwNames = [];
        if (!empty($fwIds)) {
            $fwNames = Framework::whereIn('id', $fwIds)->pluck('name')->toArray();
        }
        if ($request->filled('custom_frameworks')) {
            $customFws = array_map('trim', explode(',', $request->custom_frameworks));
            $defaultLang = ProgrammingLanguage::first() ?? ProgrammingLanguage::create(['name' => 'General', 'slug' => 'general', 'color' => '#00A859']);
            foreach ($customFws as $fwName) {
                if ($fwName) {
                    $fwObj = Framework::firstOrCreate(
                        ['name' => $fwName],
                        ['slug' => Str::slug($fwName), 'programming_language_id' => $defaultLang->id]
                    );
                    $fwIds[] = $fwObj->id;
                    $fwNames[] = $fwName;
                }
            }
        }
        $fwNames = array_values(array_unique(array_filter($fwNames)));
        $project->update(['frameworks_engines' => $fwNames]);
        if (!empty($fwIds) || $request->has('framework_ids')) {
            $project->frameworksList()->sync(array_unique(array_filter($fwIds)));
        }

        return redirect('/espace-utilisateur?section=projects')->with('success', 'Votre projet a été publié avec succès !');
    }

    public function updateProject(Request $request, int $id)
    {
        $user = Auth::guard('web')->user();
        $project = Project::where('user_id', $user->id)->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:150',
            'startup_id' => 'nullable|exists:startups,id',
            'project_type' => 'required|string|in:web,mobile,desktop,game',
            'app_types' => 'nullable|array',
            'game_types' => 'nullable|array',
            'features' => 'nullable|array',
            'youtube_presentation_url' => 'nullable|url',
            'appgallery_url' => 'nullable|url',
            'development_duration' => 'nullable|string|max:100',
            'description' => 'required|string',
            'website_url' => 'nullable|url',
            'playstore_url' => 'nullable|url',
            'appstore_url' => 'nullable|url',
            'repo_url' => 'nullable|url',
            'logo' => 'nullable|image|max:5120',
            'banner' => 'nullable|image|max:10240',
        ]);

        $features = $request->input('features', $project->features ?: ['Fonctionnalité principale']);

        $data = [
            'name' => $request->name,
            'startup_id' => $request->startup_id ?: null,
            'project_type' => $request->project_type,
            'app_types' => $request->app_types ?: [],
            'game_types' => $request->game_types ?: [],
            'features' => $features,
            'youtube_presentation_url' => $request->youtube_presentation_url ?: null,
            'appgallery_url' => $request->appgallery_url ?: null,
            'development_duration' => $request->development_duration,
            'description' => $request->description,
            'website_url' => $request->website_url,
            'playstore_url' => $request->playstore_url,
            'appstore_url' => $request->appstore_url,
            'repo_url' => $request->repo_url,
        ];

        if ($request->hasFile('logo')) {
            $data['logo'] = ImageService::saveImage($request->file('logo'), 'proj_logo');
        }
        if ($request->hasFile('banner')) {
            $data['banner'] = ImageService::saveImage($request->file('banner'), 'proj_banner');
        }

        $project->update($data);

        // Sync or create Programming Languages
        $langIds = $request->input('language_ids', []);
        $langNames = [];
        if (!empty($langIds)) {
            $langNames = ProgrammingLanguage::whereIn('id', $langIds)->pluck('name')->toArray();
        }
        if ($request->filled('custom_languages')) {
            $customLangs = array_map('trim', explode(',', $request->custom_languages));
            foreach ($customLangs as $langName) {
                if ($langName) {
                    $langObj = ProgrammingLanguage::firstOrCreate(
                        ['name' => $langName],
                        ['slug' => Str::slug($langName), 'color' => '#00A859']
                    );
                    $langIds[] = $langObj->id;
                    $langNames[] = $langName;
                }
            }
        }
        $langNames = array_values(array_unique(array_filter($langNames)));
        $project->update(['languages' => $langNames]);
        if (!empty($langIds) || $request->has('language_ids')) {
            $project->programmingLanguages()->sync(array_unique(array_filter($langIds)));
        }

        // Sync or create Frameworks
        $fwIds = $request->input('framework_ids', []);
        $fwNames = [];
        if (!empty($fwIds)) {
            $fwNames = Framework::whereIn('id', $fwIds)->pluck('name')->toArray();
        }
        if ($request->filled('custom_frameworks')) {
            $customFws = array_map('trim', explode(',', $request->custom_frameworks));
            $defaultLang = ProgrammingLanguage::first() ?? ProgrammingLanguage::create(['name' => 'General', 'slug' => 'general', 'color' => '#00A859']);
            foreach ($customFws as $fwName) {
                if ($fwName) {
                    $fwObj = Framework::firstOrCreate(
                        ['name' => $fwName],
                        ['slug' => Str::slug($fwName), 'programming_language_id' => $defaultLang->id]
                    );
                    $fwIds[] = $fwObj->id;
                    $fwNames[] = $fwName;
                }
            }
        }
        $fwNames = array_values(array_unique(array_filter($fwNames)));
        $project->update(['frameworks_engines' => $fwNames]);
        if (!empty($fwIds) || $request->has('framework_ids')) {
            $project->frameworksList()->sync(array_unique(array_filter($fwIds)));
        }

        return redirect('/espace-utilisateur?section=projects')->with('success', 'Projet mis à jour avec succès.');
    }

    public function projectGallery(int $id)
    {
        $user = Auth::guard('web')->user();
        $project = Project::with(['images' => fn($q) => $q->with('user')->orderBy('order')])->findOrFail($id);

        if (!$project->canContribute($user)) {
            abort(403, "Vous n'avez pas accès à la galerie de ce projet.");
        }

        return Inertia::render('User/Projects/Gallery', [
            'project' => $project,
            'isOwner' => $project->isOwner($user),
        ]);
    }

    public function uploadProjectImages(Request $request, int $id)
    {
        $user = Auth::guard('web')->user();
        $project = Project::findOrFail($id);

        if (!$project->canContribute($user)) {
            abort(403, "Action non autorisée.");
        }

        $request->validate([
            'image' => 'required|image|max:10240',
            'caption' => 'nullable|string|max:255',
        ]);

        $path = ImageService::saveImage($request->file('image'), 'project_gallery');
        $order = ProjectImage::where('project_id', $project->id)->count();

        $isOwner = $project->isOwner($user);
        $status = $isOwner ? 'approved' : 'pending';

        $projImage = ProjectImage::create([
            'project_id' => $project->id,
            'user_id' => $user->id,
            'image_path' => $path,
            'caption' => $request->caption ?: null,
            'order' => $order,
            'status' => $status,
        ]);

        if (!$isOwner) {
            try {
                UserMessage::create([
                    'user_id' => $project->user_id,
                    'sender_id' => $user->id,
                    'subject' => "📸 Nouvelle image en attente sur {$project->name}",
                    'message' => "Le collaborateur {$user->name} a téléversé une nouvelle image pour votre projet {$project->name}. Vous pouvez la valider depuis votre espace projet.",
                ]);
            } catch (\Throwable $e) {}

            return back()->with('success', "Image soumise avec succès ! Elle sera visible dès validation par le propriétaire du projet.");
        }

        return back()->with('success', 'Image ajoutée à la galerie avec succès !');
    }

    public function deleteProjectImage(int $imageId)
    {
        $user = Auth::guard('web')->user();
        $img = ProjectImage::with('project')->findOrFail($imageId);

        // Can delete if project owner or if it is collaborator's own pending image
        if (!$img->project->isOwner($user) && (int)$img->user_id !== (int)$user->id) {
            abort(403, "Action non autorisée.");
        }

        $img->delete();

        return back()->with('success', 'Image retirée de la galerie.');
    }

    public function projectTimeline(int $id)
    {
        $user = Auth::guard('web')->user();
        $project = Project::with(['timelines' => fn($q) => $q->with('user')->orderBy('event_date', 'desc')])->findOrFail($id);

        if (!$project->canContribute($user)) {
            abort(403, "Vous n'avez pas accès au journal de bord de ce projet.");
        }

        return Inertia::render('User/Projects/Timeline', [
            'project' => $project,
            'isOwner' => $project->isOwner($user),
        ]);
    }

    public function storeTimeline(Request $request, int $projectId)
    {
        $user = Auth::guard('web')->user();
        if ($user->isBanned()) {
            return back()->withErrors(['error' => 'Votre compte est banni.']);
        }

        $project = Project::findOrFail($projectId);

        if (!$project->canContribute($user)) {
            abort(403, "Action non autorisée sur ce projet.");
        }

        $request->validate([
            'title' => 'required|string|max:200',
            'problem_encountered' => 'nullable|string',
            'solution_applied' => 'nullable|string',
            'feature_ids' => 'nullable|array',
            'description' => 'nullable|string',
            'youtube_url' => 'nullable|url',
            'event_date' => 'nullable|date',
            'screenshot' => 'nullable|image|max:10240',
        ]);

        $images = [];
        if ($request->hasFile('screenshot')) {
            $images[] = ImageService::saveImage($request->file('screenshot'), 'timeline_shot');
        }

        $isOwner = $project->isOwner($user);
        $status = $isOwner ? 'approved' : 'pending';

        $timeline = ProjectTimeline::create([
            'project_id' => $project->id,
            'user_id' => $user->id,
            'title' => $request->title,
            'description' => $request->description,
            'problem_encountered' => $request->problem_encountered,
            'solution_applied' => $request->solution_applied,
            'feature_ids' => $request->feature_ids ?: [],
            'youtube_url' => $request->youtube_url,
            'images' => $images,
            'event_date' => $request->event_date ?: now(),
            'status' => $status,
        ]);

        if ($isOwner) {
            \App\Services\BookmarkNotificationService::notifyProjectDevlog($project, $timeline);
        } else {
            try {
                UserMessage::create([
                    'user_id' => $project->user_id,
                    'sender_id' => $user->id,
                    'subject' => "📝 Nouvelle étape Devlog en attente sur {$project->name}",
                    'message' => "Le collaborateur {$user->name} a soumis l'étape Devlog \"{$request->title}\" pour votre projet {$project->name}. Vous pouvez la valider depuis votre tableau de bord pour la publier.",
                ]);
            } catch (\Throwable $e) {}

            return redirect()->back(fallback: route('user.dashboard'))->with('success', "Votre étape de Devlog a été soumise avec succès ! Elle sera publiée dès validation par le propriétaire du projet.");
        }

        return redirect()->back(fallback: route('user.dashboard'))->with('success', 'Nouvelle étape / Résolution ajoutée au journal de bord GitHub-style !');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::guard('web')->user();

        $request->validate([
            'name' => 'required|string|max:100',
            'first_name' => 'nullable|string|max:100',
            'gender' => 'nullable|string|in:Homme,Femme,Autre',
            'country_code' => 'required|string|max:10',
            'phone' => 'nullable|string|max:30',
            'birth_date' => 'nullable|date',
            'marital_status' => 'nullable|string|in:Célibataire,Marié(e),Divorcé(e),Autre',
            'bio' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|max:5120',
        ]);

        $data = [
            'name' => $request->name,
            'first_name' => $request->first_name,
            'gender' => $request->gender,
            'country_code' => $request->country_code,
            'phone' => $request->phone,
            'birth_date' => $request->birth_date,
            'marital_status' => $request->marital_status,
            'bio' => $request->bio,
        ];

        if ($request->hasFile('avatar')) {
            $data['avatar'] = ImageService::saveImage($request->file('avatar'), 'user_avatar');
        }

        $user->update($data);

        return back()->with('success', 'Votre profil a été mis à jour avec succès.');
    }

    public function markMessageRead(int $id)
    {
        $user = Auth::guard('web')->user();
        $message = UserMessage::where('user_id', $user->id)->findOrFail($id);
        $message->update([
            'is_read' => true,
            'read_at' => $message->read_at ?? now(),
        ]);

        return back()->with('success', 'Message marqué comme lu.');
    }

    public function toggleReaction(Request $request, int $id)
    {
        $user = Auth::guard('web')->user();
        $message = UserMessage::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)->orWhere('sender_id', $user->id);
        })->findOrFail($id);

        $emoji = $request->input('emoji');
        $validEmojis = ['👍', '❤️', '🚀', '🔥', '👏'];
        if (!in_array($emoji, $validEmojis)) {
            return back()->withErrors(['error' => 'Emoji invalide.']);
        }

        $reactions = $message->reactions ?? [];
        if (!isset($reactions[$emoji]) || !is_array($reactions[$emoji])) {
            $reactions[$emoji] = [];
        }

        if (in_array($user->id, $reactions[$emoji])) {
            $reactions[$emoji] = array_values(array_diff($reactions[$emoji], [$user->id]));
            if (empty($reactions[$emoji])) {
                unset($reactions[$emoji]);
            }
        } else {
            $reactions[$emoji][] = $user->id;
        }

        $message->update(['reactions' => $reactions]);

        return back();
    }

    public function deleteMessage(Request $request, int $id)
    {
        $user = Auth::guard('web')->user();
        $message = UserMessage::findOrFail($id);

        if ((int)$message->sender_id !== (int)$user->id) {
            abort(403, "Action non autorisée.");
        }

        $message->delete();

        return back()->with('success', 'Message supprimé.');
    }

    public function deleteDocument(int $id)
    {
        $user = Auth::guard('web')->user();
        $doc = UserDocument::where('user_id', $user->id)->findOrFail($id);
        $doc->delete();

        return back()->with('success', 'Document supprimé.');
    }

    public function createJobOffer()
    {
        $user = Auth::guard('web')->user();
        if ($user->isBanned()) {
            return back()->withErrors(['error' => 'Votre compte est restreint.']);
        }

        $domains = [
            'Développement Web & Mobile',
            'Réseaux & Télécommunications',
            'Cybersécurité & Systèmes',
            'Data Science & Intelligence Artificielle',
            'DevOps & Cloud Computing',
            'UI/UX Design & Gestion de Produit',
            'Support & Maintenance Informatique',
            'Gestion de Projet IT & Méthodes Agiles',
            'Administration Bases de Données (DBA)',
        ];

        $contractTypes = [
            'CDI' => 'Contrat à Durée Indéterminée (CDI)',
            'CDD' => 'Contrat à Durée Déterminée (CDD)',
            'Stage de vacances professionnel' => 'Stage de vacances professionnel',
            'Stage pré-emploi' => 'Stage pré-emploi',
            'Freelance / Prestation' => 'Freelance / Prestation',
            'Alternance' => 'Alternance',
        ];

        $cities = ['Douala', 'Yaoundé', 'Bafoussam', 'Garoua', 'Maroua', 'Buea', 'Limbe', 'Kribi', 'Télétravail'];

        return Inertia::render('User/Jobs/Create', [
            'domains' => $domains,
            'contractTypes' => $contractTypes,
            'cities' => $cities,
        ]);
    }

    public function storeJobOffer(Request $request)
    {
        $user = Auth::guard('web')->user();
        if ($user->isBanned()) {
            return back()->withErrors(['error' => 'Votre compte est restreint.']);
        }

        $request->validate([
            'title' => 'required|string|max:200',
            'domains' => 'required|array|min:1',
            'contract_type' => 'required|string',
            'salary_type' => 'required|in:negotiable,fixed',
            'salary_amount' => 'nullable|numeric|min:0',
            'city' => 'required|string',
            'deadline_date' => 'nullable|date|after_or_equal:today',
            'accepts_cv' => 'required|boolean',
            'accepts_cover_letter' => 'nullable|boolean',
            'short_description' => 'required|string|max:500',
            'description' => 'required|string',
            'has_online_test' => 'nullable|boolean',
        ], [
            'domains.required' => 'Veuillez sélectionner au moins un domaine informatique.',
            'deadline_date.after_or_equal' => 'La date limite ne peut pas être une date passée.',
        ]);

        $hasTest = (bool) $request->has_online_test;

        $job = JobOffer::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . Str::random(5),
            'domain' => $request->domains,
            'contract_type' => $request->contract_type,
            'salary_type' => $request->salary_type,
            'salary_amount' => $request->salary_type === 'fixed' ? $request->salary_amount : null,
            'city' => $request->city,
            'accepts_cv' => true, // obligatoire et coché par défaut
            'accepts_cover_letter' => (bool) $request->accepts_cover_letter,
            'short_description' => $request->short_description,
            'description' => $request->description,
            'deadline_date' => $request->deadline_date,
            'has_online_test' => $hasTest,
            'status' => 'pending_validation',
        ]);

        if ($hasTest) {
            return redirect()->route('user.jobs.questions', $job->id)->with('success', 'Offre enregistrée ! Ajoutez maintenant les questions de l\'évaluation en ligne.');
        }

        return redirect('/espace-utilisateur?section=jobs')->with('success', 'Votre offre d\'emploi a été enregistrée et envoyée à l\'administrateur pour validation. Statut : En cours de validation.');
    }

    public function editJobOffer(int $id)
    {
        $user = Auth::guard('web')->user();
        $job = JobOffer::where('user_id', $user->id)->with('questions')->findOrFail($id);

        if ($job->applications()->count() > 0) {
            return redirect('/espace-utilisateur?section=jobs')->with('error', 'Cette offre a déjà reçu des candidatures et ne peut plus être modifiée afin de préserver l\'intégrité du processus de recrutement.');
        }

        $domains = [
            'Développement Web & Mobile',
            'Réseaux & Télécommunications',
            'Cybersécurité & Systèmes',
            'Data Science & Intelligence Artificielle',
            'DevOps & Cloud Computing',
            'UI/UX Design & Gestion de Produit',
            'Support & Maintenance Informatique',
            'Gestion de Projet IT & Méthodes Agiles',
            'Administration Bases de Données (DBA)',
        ];

        $contractTypes = [
            'CDI' => 'Contrat à Durée Indéterminée (CDI)',
            'CDD' => 'Contrat à Durée Déterminée (CDD)',
            'Stage de vacances professionnel' => 'Stage de vacances professionnel',
            'Stage pré-emploi' => 'Stage pré-emploi',
            'Freelance / Prestation' => 'Freelance / Prestation',
            'Alternance' => 'Alternance',
        ];

        $cities = ['Douala', 'Yaoundé', 'Bafoussam', 'Garoua', 'Maroua', 'Buea', 'Limbe', 'Kribi', 'Télétravail'];

        return Inertia::render('User/Jobs/Edit', [
            'job' => $job,
            'domains' => $domains,
            'contractTypes' => $contractTypes,
            'cities' => $cities,
        ]);
    }

    public function updateJobOffer(Request $request, int $id)
    {
        $user = Auth::guard('web')->user();
        $job = JobOffer::where('user_id', $user->id)->findOrFail($id);

        if ($job->applications()->count() > 0) {
            return redirect('/espace-utilisateur?section=jobs')->with('error', 'Cette offre a déjà reçu des candidatures et ne peut plus être modifiée.');
        }

        $request->validate([
            'title' => 'required|string|max:200',
            'domains' => 'required|array|min:1',
            'contract_type' => 'required|string',
            'salary_type' => 'required|in:negotiable,fixed',
            'salary_amount' => 'nullable|numeric|min:0',
            'city' => 'required|string',
            'deadline_date' => 'nullable|date',
            'accepts_cover_letter' => 'nullable|boolean',
            'short_description' => 'required|string|max:500',
            'description' => 'required|string',
            'has_online_test' => 'nullable|boolean',
        ]);

        $hasTest = (bool) $request->has_online_test;

        $job->update([
            'title' => $request->title,
            'domain' => $request->domains,
            'contract_type' => $request->contract_type,
            'salary_type' => $request->salary_type,
            'salary_amount' => $request->salary_type === 'fixed' ? $request->salary_amount : null,
            'city' => $request->city,
            'accepts_cover_letter' => (bool) $request->accepts_cover_letter,
            'short_description' => $request->short_description,
            'description' => $request->description,
            'deadline_date' => $request->deadline_date,
            'has_online_test' => $hasTest,
        ]);

        if ($hasTest) {
            return redirect()->route('user.jobs.questions', $job->id)->with('success', 'Offre mise à jour ! Vous pouvez modifier les questions de l\'évaluation.');
        }

        return redirect('/espace-utilisateur?section=jobs')->with('success', 'Offre d\'emploi mise à jour avec succès.');
    }

    public function jobQuestions(int $id)
    {
        $user = Auth::guard('web')->user();
        $job = JobOffer::where('user_id', $user->id)->with('questions')->findOrFail($id);

        return Inertia::render('User/Jobs/Questions', [
            'job' => $job,
        ]);
    }

    public function saveJobQuestions(Request $request, int $id)
    {
        $user = Auth::guard('web')->user();
        $job = JobOffer::where('user_id', $user->id)->findOrFail($id);

        $request->validate([
            'questions' => 'required|array|min:1',
            'questions.*.question_text' => 'required|string',
            'questions.*.question_type' => 'required|in:text,qcm',
            'questions.*.options' => 'nullable|array',
            'questions.*.correct_answer' => 'nullable|string',
        ]);

        // Delete previous questions and replace
        $job->questions()->delete();

        foreach ($request->questions as $idx => $qData) {
            $options = null;
            if ($qData['question_type'] === 'qcm' && !empty($qData['options'])) {
                $options = array_values(array_filter(array_map('trim', $qData['options'])));
            }

            JobQuestion::create([
                'job_offer_id' => $job->id,
                'question_text' => $qData['question_text'],
                'question_type' => $qData['question_type'],
                'options' => $options,
                'correct_answer' => $qData['correct_answer'] ?? null,
                'order' => $idx + 1,
            ]);
        }

        $job->update(['status' => 'pending_validation', 'has_online_test' => true]);

        return redirect('/espace-utilisateur?section=jobs')->with('success', 'Test d\'évaluation enregistré ! Votre offre est désormais en cours de validation par l\'administration.');
    }

    public function jobApplicants(int $id)
    {
        $user = Auth::guard('web')->user();
        $job = JobOffer::where('user_id', $user->id)
            ->with(['questions', 'applications.interview', 'applications.user'])
            ->findOrFail($id);

        return Inertia::render('User/Jobs/Applicants', [
            'job' => $job,
        ]);
    }

    public function examineTest(int $applicationId)
    {
        $user = Auth::guard('web')->user();
        $application = JobApplication::whereHas('jobOffer', fn($q) => $q->where('user_id', $user->id))->findOrFail($applicationId);

        $application->update(['examined_at' => now(), 'status' => 'under_review']);

        return back()->with('success', 'Test d\'évaluation examiné avec succès.');
    }

    public function scheduleInterview(Request $request, int $applicationId)
    {
        $user = Auth::guard('web')->user();
        $application = JobApplication::whereHas('jobOffer', fn($q) => $q->where('user_id', $user->id))
            ->with('jobOffer')
            ->findOrFail($applicationId);

        // Validation against past dates and past times
        $today = date('Y-m-d');
        $currentTime = date('H:i');

        $request->validate([
            'interview_date' => 'required|date|after_or_equal:today',
            'interview_time' => 'required|string',
            'location' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'email_message' => 'nullable|string',
        ], [
            'interview_date.after_or_equal' => 'Vous ne pouvez pas choisir une date déjà passée.',
        ]);

        if ($request->interview_date === $today && $request->interview_time < $currentTime) {
            return back()->withErrors(['interview_time' => 'Vous ne pouvez pas choisir une heure déjà passée pour aujourd\'hui.']);
        }

        // If online test exists, recruiter MUST have examined it
        if ($application->jobOffer->has_online_test && empty($application->examined_at)) {
            return back()->withErrors(['error' => 'Cette offre comporte un test en ligne : vous devez d\'abord examiner les réponses du candidat avant de convoquer un entretien.']);
        }

        // Prevent duplicate interviews
        if ($application->interview()->exists()) {
            return back()->withErrors(['error' => 'Un entretien a déjà été programmé pour ce candidat.']);
        }

        $interview = JobInterview::create([
            'job_application_id' => $application->id,
            'interview_date' => $request->interview_date,
            'interview_time' => $request->interview_time,
            'location' => $request->location,
            'notes' => $request->notes,
            'is_sent' => true,
        ]);

        $application->update(['status' => 'interview_scheduled']);

        // Send email to candidate
        try {
            Mail::send('emails.job_interview', [
                'job' => $application->jobOffer,
                'application' => $application,
                'interview' => $interview,
                'customMessage' => $request->email_message,
            ], function ($message) use ($application) {
                $message->to($application->email, $application->name)
                    ->subject("Convocation à un entretien - {$application->jobOffer->title} (Tech237)");
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Erreur envoi email convocation entretien : " . $e->getMessage());
        }

        return back()->with('success', "Entretien programmé pour le {$request->interview_date} à {$request->interview_time}. Un email de convocation officiel a été transmis à {$application->email}.");
    }

    public function rejectApplicant(Request $request, int $applicationId)
    {
        $user = Auth::guard('web')->user();
        $application = JobApplication::whereHas('jobOffer', fn($q) => $q->where('user_id', $user->id))
            ->with('jobOffer')
            ->findOrFail($applicationId);

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
            'email_message' => 'nullable|string',
        ]);

        $application->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        return back()->with('success', 'La candidature a été rejetée et un email explicatif a été transmis au candidat.');
    }

    public function createConference()
    {
        $user = Auth::guard('web')->user();
        if ($user->isBanned()) {
            return back()->withErrors(['error' => 'Votre compte est restreint.']);
        }

        $cities = ['Douala', 'Yaoundé', 'Bafoussam', 'Garoua', 'Maroua', 'Buea', 'Limbe', 'Kribi', 'Paris', 'En ligne (Webinaire)'];

        return Inertia::render('User/Conferences/Create', [
            'cities' => $cities,
        ]);
    }

    public function storeConference(Request $request)
    {
        $user = Auth::guard('web')->user();
        if ($user->isBanned()) {
            return back()->withErrors(['error' => 'Votre compte est restreint.']);
        }

        $request->validate([
            'title_fr' => 'required|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'event_date' => 'required|date|after_or_equal:today',
            'end_date' => 'nullable|date|after_or_equal:event_date',
            'deadline_registration' => 'nullable|date',
            'city' => 'required|string|max:100',
            'location' => 'nullable|string|max:255',
            'is_in_person' => 'nullable|boolean',
            'is_free' => 'nullable|boolean',
            'price' => 'nullable|string|max:100',
            'website_url' => 'nullable|string|max:500',
            'description_fr' => 'required|string',
            'description_en' => 'nullable|string',
            'accepts_registrations' => 'nullable|boolean',
            'cover_image' => 'nullable|image|max:10240',
        ], [
            'title_fr.required' => 'Le titre en français est obligatoire.',
            'event_date.after_or_equal' => 'La date de début ne peut pas être une date passée.',
        ]);

        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $coverPath = ImageService::saveImage($request->file('cover_image'), 'conference_cover');
        }

        $slug = Str::slug($request->title_fr) . '-' . Str::random(5);

        Conference::create([
            'user_id' => $user->id,
            'title_fr' => $request->title_fr,
            'title_en' => $request->title_en ?: null,
            'slug' => $slug,
            'cover_image' => $coverPath,
            'event_date' => $request->event_date,
            'end_date' => $request->end_date ?: $request->event_date,
            'deadline_registration' => $request->deadline_registration ?: null,
            'city' => $request->city,
            'location' => $request->location ?: null,
            'is_in_person' => $request->has('is_in_person') ? (bool) $request->is_in_person : true,
            'is_free' => $request->has('is_free') ? (bool) $request->is_free : true,
            'price' => $request->is_free ? null : ($request->price ?: null),
            'website_url' => $request->website_url ?: null,
            'description_fr' => $request->description_fr,
            'description_en' => $request->description_en ?: null,
            'accepts_registrations' => (bool) $request->accepts_registrations,
            'status' => 'pending_validation',
            'is_official_admin' => false,
        ]);

        return redirect('/espace-utilisateur?section=conferences')
            ->with('success', 'Votre événement / conférence a été créé avec succès ! Il est actuellement en cours de validation par l\'administration.');
    }

    public function editConference(int $id)
    {
        $user = Auth::guard('web')->user();
        $conference = Conference::where('user_id', $user->id)->findOrFail($id);

        $cities = ['Douala', 'Yaoundé', 'Bafoussam', 'Garoua', 'Maroua', 'Buea', 'Limbe', 'Kribi', 'Paris', 'En ligne (Webinaire)'];

        return Inertia::render('User/Conferences/Edit', [
            'conference' => $conference,
            'cities' => $cities,
        ]);
    }

    public function updateConference(Request $request, int $id)
    {
        $user = Auth::guard('web')->user();
        $conference = Conference::where('user_id', $user->id)->findOrFail($id);

        $request->validate([
            'title_fr' => 'required|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'event_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:event_date',
            'deadline_registration' => 'nullable|date',
            'city' => 'required|string|max:100',
            'location' => 'nullable|string|max:255',
            'is_in_person' => 'nullable|boolean',
            'is_free' => 'nullable|boolean',
            'price' => 'nullable|string|max:100',
            'website_url' => 'nullable|string|max:500',
            'description_fr' => 'required|string',
            'description_en' => 'nullable|string',
            'accepts_registrations' => 'nullable|boolean',
            'cover_image' => 'nullable|image|max:10240',
        ]);

        $coverPath = $conference->cover_image;
        if ($request->hasFile('cover_image')) {
            $coverPath = ImageService::saveImage($request->file('cover_image'), 'conference_cover');
        }

        $conference->update([
            'title_fr' => $request->title_fr,
            'title_en' => $request->title_en ?: null,
            'cover_image' => $coverPath,
            'event_date' => $request->event_date,
            'end_date' => $request->end_date ?: $request->event_date,
            'deadline_registration' => $request->deadline_registration ?: null,
            'city' => $request->city,
            'location' => $request->location ?: null,
            'is_in_person' => $request->has('is_in_person') ? (bool) $request->is_in_person : true,
            'is_free' => $request->has('is_free') ? (bool) $request->is_free : true,
            'price' => $request->is_free ? null : ($request->price ?: null),
            'website_url' => $request->website_url ?: null,
            'description_fr' => $request->description_fr,
            'description_en' => $request->description_en ?: null,
            'accepts_registrations' => (bool) $request->accepts_registrations,
        ]);

        return redirect('/espace-utilisateur?section=conferences')
            ->with('success', 'Votre événement / conférence a été mis à jour avec succès.');
    }

    public function conferenceParticipants(int $id)
    {
        $user = Auth::guard('web')->user();
        $conference = Conference::where('user_id', $user->id)
            ->with(['registrations' => fn($q) => $q->latest()])
            ->findOrFail($id);

        return Inertia::render('User/Conferences/Participants', [
            'conference' => $conference,
        ]);
    }

    public function sendConfirmationEmail(int $regId)
    {
        $user = Auth::guard('web')->user();
        $reg = ConferenceRegistration::whereHas('conference', fn($q) => $q->where('user_id', $user->id))
            ->with('conference')
            ->findOrFail($regId);

        if ($reg->is_confirmed) {
            return back()->withErrors(['error' => 'Un email de confirmation a déjà été transmis à ce participant.']);
        }

        $settings = SiteSetting::current();

        try {
            Mail::send('emails.conference_confirmation', [
                'conference' => $reg->conference,
                'registration' => $reg,
                'settings' => $settings,
            ], function ($message) use ($reg, $settings) {
                $message->to($reg->email, $reg->first_name . ' ' . $reg->name)
                    ->subject("Confirmation officielle de votre inscription - {$reg->conference->title_fr}");
            });
        } catch (\Throwable $e) {}

        $reg->update([
            'is_confirmed' => true,
            'confirmed_at' => now(),
        ]);

        return back()->with('success', "Email de confirmation officiel transmis à {$reg->email} !");
    }

    public function sendBulkConfirmationEmails(int $conferenceId, Request $request)
    {
        $user = Auth::guard('web')->user();
        $conference = Conference::where('user_id', $user->id)->findOrFail($conferenceId);

        $selectedIds = $request->input('selected_ids', []);
        $query = $conference->registrations()->where('is_confirmed', false);

        if (!empty($selectedIds) && is_array($selectedIds)) {
            $query->whereIn('id', $selectedIds);
        }

        $pendingRegistrations = $query->get();
        $count = 0;
        $settings = SiteSetting::current();

        foreach ($pendingRegistrations as $reg) {
            try {
                Mail::send('emails.conference_confirmation', [
                    'conference' => $conference,
                    'registration' => $reg,
                    'settings' => $settings,
                ], function ($message) use ($reg, $conference) {
                    $message->to($reg->email, $reg->first_name . ' ' . $reg->name)
                        ->subject("Confirmation officielle de votre inscription - {$conference->title_fr}");
                });
            } catch (\Throwable $e) {}

            $reg->update([
                'is_confirmed' => true,
                'confirmed_at' => now(),
            ]);
            $count++;
        }

        return back()->with('success', "Envoi terminé : {$count} email(s) de confirmation transmis avec succès !");
    }

    public function sendMessage(Request $request)
    {
        $sender = Auth::guard('web')->user();
        if ($sender->isBanned()) {
            return back()->withErrors(['error' => 'Votre compte est restreint.']);
        }

        $validated = $request->validate([
            'recipient_id' => 'nullable|exists:users,id',
            'subject' => 'required|string|max:200',
            'message' => 'required|string|max:5000',
            'attachment' => 'nullable|file|max:10240|mimes:jpeg,png,jpg,webp,gif,pdf,doc,docx,txt,zip',
        ], [
            'subject.required' => 'Le sujet est requis.',
            'message.required' => 'Le message ne peut pas être vide.',
            'attachment.max' => 'La pièce jointe ne doit pas dépasser 10 Mo.',
            'attachment.mimes' => 'Format de pièce jointe non autorisé (images, PDF, Word, TXT, ZIP autorisés).',
        ]);

        $recipientId = $validated['recipient_id'] ?? null;
        if (!$recipientId || (int)$recipientId === (int)$sender->id) {
            $recipientId = \App\Models\User::where('id', '!=', $sender->id)->value('id') ?? $sender->id;
        }

        $attachmentPath = null;
        $attachmentName = null;
        $attachmentType = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentName = $file->getClientOriginalName();
            $ext = strtolower($file->getClientOriginalExtension() ?: 'dat');
            $attachmentType = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']) ? 'image' : 'document';
            $attachmentPath = ImageService::saveImage($file, 'msg_att');
        }

        UserMessage::create([
            'user_id' => $recipientId,
            'sender_id' => $sender->id,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'attachment_type' => $attachmentType,
            'is_read' => false,
        ]);

        // Send notification email to the recipient
        $recipient = \App\Models\User::find($recipientId);
        if ($recipient && $recipient->email && filter_var($recipient->email, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::send('emails.new_message_notification', [
                    'recipient' => $recipient,
                    'sender' => $sender,
                    'msgSubject' => $validated['subject'],
                    'msgContent' => $validated['message'],
                    'replyUrl' => route('user.dashboard', ['section' => 'messages']),
                ], function ($m) use ($recipient, $sender) {
                    $m->to($recipient->email)
                      ->subject("💬 Nouveau message privé de {$sender->name} sur Tech237");
                });
            } catch (\Throwable $e) {
                Log::error("Erreur envoi notification email message: " . $e->getMessage());
            }
        }

        return back()->with('success', 'Votre message privé a été envoyé avec succès !');
    }
}
