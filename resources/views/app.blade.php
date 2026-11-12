<!DOCTYPE html>
<html lang="{{ session('locale', 'fr') }}" prefix="og: http://ogp.me/ns#">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title inertia>{{ config('app.name', 'Tech237') }} - Là où les idées deviennent des projets</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo.png') }}">

    <!-- 100% Offline Local Assets: Poppins Font, Bootstrap 5, Bootstrap Icons, Tech237 Front Theme -->
    <link rel="stylesheet" href="{{ asset('assets/css/poppins.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/melty-front.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}">

    <!-- Google Fonts for Portfolio Typography Customization (Montserrat, Roboto, Jakarta, etc.) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;700&family=Fira+Code:wght@400;600&family=Inter:wght@400;600;700&family=Lexend:wght@400;600;700&family=Montserrat:wght@400;600;700;800&family=Outfit:wght@400;600;700&family=Playfair+Display:ital,wght@0,600;0,800;1,400&family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Raleway:wght@400;600;700&family=Roboto:wght@400;500;700&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">

    <!-- Search Engine & Social Media OpenGraph / Twitter Meta Tags (Exclusively for Front-End) -->
    @php
        $settings = \App\Models\SiteSetting::current();
        $baseUrl = rtrim(request()->getSchemeAndHttpHost() ?: config('app.url', url('/')), '/');

        $buildAbsoluteImageUrl = function (?string $path) use ($baseUrl): ?string {
            if (empty($path)) return null;
            $path = trim($path);
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }
            $cleanPath = ltrim($path, '/');
            $encodedSegments = array_map('rawurlencode', explode('/', $cleanPath));
            return $baseUrl . '/' . implode('/', $encodedSegments);
        };

        $defaultImg = $buildAbsoluteImageUrl($settings->seo_image ?: 'assets/images/logo.png') ?: asset('assets/images/logo.png');
        $siteName = $settings->site_name ?? 'Tech237';
        $defaultDesc = $settings->meta_description ?? 'Tech237 - Là où les idées deviennent des projets. Le média tech, innovation et startups de référence au Cameroun.';

        $extractFirstHtmlImage = function (?string $html): ?string {
            if (empty($html)) return null;
            if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $matches)) {
                return $matches[1] ?? null;
            }
            return null;
        };

        $formatImage = function (?string $path, ?string $fallbackHtml = null) use ($buildAbsoluteImageUrl, $extractFirstHtmlImage, $defaultImg): string {
            $resolved = $buildAbsoluteImageUrl($path);
            if ($resolved) return $resolved;
            if ($fallbackHtml) {
                $htmlImg = $buildAbsoluteImageUrl($extractFirstHtmlImage($fallbackHtml));
                if ($htmlImg) return $htmlImg;
            }
            return $defaultImg;
        };

        $formatText = function (?string $text, int $limit = 160) use ($defaultDesc): string {
            if (empty($text)) return $defaultDesc;
            return \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($text))), $limit);
        };

        $seoTitle = $siteName . ' - Là où les idées deviennent des projets';
        $seoDesc = $defaultDesc;
        $seoImg = $defaultImg;
        $ogType = 'website';

        try {
            if (Request::routeIs('article.show')) {
                $slug = request()->route('slug');
                $article = \App\Models\Article::with('category')->where('slug', $slug)->first();
                if ($article) {
                    $seoTitle = ($article->title_fr ?: $article->title_en) . ' | ' . $siteName;
                    $seoDesc = $formatText($article->summary_fr ?: $article->content_fr);
                    $seoImg = $formatImage($article->featured_image, $article->content_fr);
                    $ogType = 'article';
                }
            }
            elseif (Request::routeIs('entrepreneurs.current')) {
                $entrepreneur = \App\Models\Entrepreneur::where('is_current_month', true)->first()
                    ?? \App\Models\Entrepreneur::latest()->first();
                if ($entrepreneur) {
                    $seoTitle = "Entrepreneur du Mois : " . $entrepreneur->name . ($entrepreneur->startup_name ? ' (' . $entrepreneur->startup_name . ')' : '') . ' | ' . $siteName;
                    $seoDesc = $formatText($entrepreneur->bio_fr ?: $entrepreneur->story_fr);
                    $seoImg = $formatImage($entrepreneur->photo ?: $entrepreneur->cover_image);
                } else {
                    $seoTitle = "L'Entrepreneur du Mois | " . $siteName;
                    $seoDesc = "Découvrez chaque mois le portrait d'un entrepreneur tech innovant au Cameroun.";
                }
            }
            elseif (Request::routeIs('entrepreneurs.show')) {
                $id = request()->route('id');
                $entrepreneur = \App\Models\Entrepreneur::find($id);
                if ($entrepreneur) {
                    $seoTitle = "Portrait Entrepreneur : " . $entrepreneur->name . ' | ' . $siteName;
                    $seoDesc = $formatText($entrepreneur->bio_fr ?: $entrepreneur->story_fr);
                    $seoImg = $formatImage($entrepreneur->photo ?: $entrepreneur->cover_image);
                }
            }
            elseif (Request::routeIs('conferences.show')) {
                $slug = request()->route('slug');
                $conf = \App\Models\Conference::where('slug', $slug)->first();
                if ($conf) {
                    $seoTitle = "Événement : " . $conf->title . ($conf->city ? ' à ' . $conf->city : '') . ' | ' . $siteName;
                    $seoDesc = $formatText($conf->description);
                    $seoImg = $formatImage($conf->cover_image ?: $conf->banner);
                }
            }
            elseif (Request::routeIs('jobs.show')) {
                $slug = request()->route('slug');
                $job = \App\Models\JobOffer::where('slug', $slug)->first();
                if ($job) {
                    $seoTitle = "Recrutement IT : " . $job->title . ($job->company_name ? ' chez ' . $job->company_name : '') . ' | ' . $siteName;
                    $seoDesc = $formatText($job->description);
                    $seoImg = $formatImage($job->company_logo);
                }
            }
            elseif (Request::routeIs('games.index')) {
                $seoTitle = "Tests Jeux Vidéo - Critiques, Avis & Notes | " . $siteName;
                $seoDesc = "Retrouvez tous les tests indépendants, avis détaillés et notes sur les dernières sorties jeux vidéo console, PC et mobile.";
                $seoImg = asset('assets/images/image_jeux_video.jpg');
            }
            elseif (Request::routeIs('games.show')) {
                $slug = request()->route('slug');
                $game = \App\Models\GameReview::where('slug', $slug)->first();
                if ($game) {
                    $seoTitle = "Test : " . $game->game_name . " (" . $game->final_score . "/20) | " . $siteName;
                    $seoDesc = $formatText($game->summary_fr ?: $game->content_fr);
                    $seoImg = $formatImage($game->cover_image, $game->content_fr);
                    $ogType = 'article';
                }
            }
            elseif (Request::routeIs('apps.index')) {
                $seoTitle = "Tests Applications Mobiles & Web Innovantes | " . $siteName;
                $seoDesc = "Découvrez nos tests et analyses détaillées des meilleures applications mobiles et plateformes web utiles au quotidien.";
                $seoImg = $defaultImg;
            }
            elseif (Request::routeIs('apps.show')) {
                $slug = request()->route('slug');
                $app = \App\Models\AppReview::where('slug', $slug)->first();
                if ($app) {
                    $seoTitle = "Test : " . $app->app_name . " (" . $app->final_score . "/20) | " . $siteName;
                    $seoDesc = $formatText($app->summary_fr ?: $app->content_fr);
                    $seoImg = $formatImage($app->cover_image, $app->content_fr);
                    $ogType = 'article';
                }
            }
            elseif (Request::routeIs('interviews.index')) {
                $seoTitle = "Grandes Interviews Tech & Rencontres Inspirantes | " . $siteName;
                $seoDesc = "Retrouvez nos grands entretiens avec les fondateurs de startups, experts en IA et leaders de l'écosystème numérique africain.";
                $seoImg = $defaultImg;
            }
            elseif (Request::routeIs('interviews.show')) {
                $slug = request()->route('slug');
                $interview = \App\Models\Interview::where('slug', $slug)->first();
                if ($interview) {
                    $seoTitle = "Interview : " . $interview->interviewee_name . " - " . $interview->title_fr . ' | ' . $siteName;
                    $seoDesc = $formatText($interview->summary_fr ?: $interview->bio_fr);
                    $seoImg = $formatImage($interview->cover_image ?: $interview->interviewee_photo);
                }
            }
            elseif (Request::routeIs('projects.index')) {
                $seoTitle = "Projets & Produits Tech Made in Cameroon | " . $siteName;
                $seoDesc = "Explorez les projets logiciels, plateformes SaaS et innovations technologiques conçues et propulsées au Cameroun.";
                $seoImg = $defaultImg;
            }
            elseif (Request::routeIs('projects.show')) {
                $slug = request()->route('slug');
                $project = \App\Models\Project::where('slug', $slug)->first();
                if ($project) {
                    $seoTitle = "Projet : " . $project->title . ($project->category ? ' (' . $project->category . ')' : '') . ' | ' . $siteName;
                    $seoDesc = $formatText($project->short_description ?: $project->description);
                    $seoImg = $formatImage($project->banner ?: $project->logo);
                }
            }
            elseif (Request::routeIs('startups.index')) {
                $seoTitle = "Annuaire des Startups Camerounaises & Ecosystème Tech | " . $siteName;
                $seoDesc = "Découvrez le répertoire exclusif des startups du Cameroun : Fintech, Agritech, Edtech, Santé et GreenTech en plein essor.";
                $seoImg = $defaultImg;
            }
            elseif (Request::routeIs('startups.show')) {
                $slug = request()->route('slug');
                $startup = \App\Models\Startup::where('slug', $slug)->first();
                if ($startup) {
                    $seoTitle = "Startup : " . $startup->name . ($startup->sector ? ' - ' . $startup->sector : '') . ' | ' . $siteName;
                    $seoDesc = $formatText($startup->pitch ?: $startup->description);
                    $seoImg = $formatImage($startup->banner ?: $startup->logo);
                }
            }
            elseif (Request::routeIs('rubrique.show')) {
                $slug = request()->route('slug');
                $rubrique = \App\Models\Rubrique::where('slug', $slug)->first();
                if ($rubrique) {
                    $seoTitle = "Rubrique " . $rubrique->name_fr . " | " . $siteName;
                    $seoDesc = "Toutes les actualités, décryptages et dossiers exclusifs sur " . $rubrique->name_fr . " sur " . $siteName . ".";
                    $seoImg = $formatImage($rubrique->banner_image);
                }
            }
            elseif (Request::routeIs('dossier.show')) {
                $slug = request()->route('slug');
                $dossier = \App\Models\Dossier::where('slug', $slug)->first();
                if ($dossier) {
                    $seoTitle = "Grand Dossier : " . $dossier->title_fr . " | " . $siteName;
                    $seoDesc = $formatText($dossier->description_fr);
                    $seoImg = $formatImage($dossier->cover_image);
                }
            }
            elseif (Request::routeIs('support')) {
                $seoTitle = "Nous Soutenir - Contribuez au Hub Média & Tech | " . $siteName;
                $seoDesc = "Participez au développement de Tech237 via Orange Money, MTN MoMo ou virement bancaire pour soutenir l'innovation au Cameroun.";
                $seoImg = $defaultImg;
            }
            elseif (Request::routeIs('terms')) {
                $seoTitle = "Conditions Générales d'Utilisation (CGU) & Mentions Légales | " . $siteName;
                $seoDesc = "Consultez l'ensemble des règles régissant l'utilisation de la plateforme média et des services communautaires de Tech237.";
                $seoImg = $defaultImg;
            }
            elseif (Request::routeIs('privacy')) {
                $seoTitle = "Politique de Confidentialité & Protection des Données | " . $siteName;
                $seoDesc = "Nos engagements rigoureux concernant la confidentialité, le respect de votre vie privée et la gestion des données personnelles.";
                $seoImg = $defaultImg;
            }
            elseif (Request::routeIs('pocs.index')){
                $seoTitle = "Les POCs de la Communauté | " . $siteName;
                $seoDesc = "Partagez de courtes actualités dans le domaine de la tech avec les autres membres de la communauté. ";
                $seoImg = $defaultImg;
            }
            elseif (Request::routeIs('pocs.show')) {
                $slug = request()->route('slug');
                $poc = \App\Models\Poc::where('slug', $slug)->orWhere('id', $slug)->first();
                if ($poc) {
                    $seoTitle = $poc->title . " | POC " . $siteName;
                    $seoDesc = $formatText($poc->summary ?: $poc->content);
                    $seoImg = $formatImage($poc->cover_image, $poc->content);
                    $ogType = 'article';
                }
            }
            elseif (Request::routeIs('user.portfolio') || Request::routeIs('user.portfolio.short') || Request::routeIs('user.cv')) {
                $username = request()->route('username');
                $portfolioUser = \App\Models\User::where('username', $username)->first();
                if ($portfolioUser) {
                    $headline = $portfolioUser->headline ? " — " . $portfolioUser->headline : " • Profil & Portfolio Tech";
                    $seoTitle = $portfolioUser->name . $headline . " | " . $siteName;
                    $seoDesc = $formatText($portfolioUser->bio ?: ($portfolioUser->name . " est un développeur et bâtisseur tech sur " . $siteName . ". Découvrez ses projets, POCs, compétences et réalisations."));
                    $seoImg = $formatImage($portfolioUser->avatar);
                    $ogType = 'profile';
                }
            }
            elseif (Request::routeIs('forms.public.show')) {
                $slug = request()->route('slug');
                $form = \App\Models\Form::with('user')->where('slug', $slug)->orWhere('uuid', $slug)->first();
                if ($form) {
                    $creatorName = $form->user->name ?? $siteName;
                    $seoTitle = "Formulaire : " . $form->title . " | " . $siteName;
                    $seoDesc = $formatText($form->description ?: ("Participez au formulaire « " . $form->title . " » créé par " . $creatorName . " sur " . $siteName . "."));
                    $seoImg = $formatImage($form->cover_image ?: ($form->user->avatar ?? $defaultImg));
                    $ogType = 'website';
                }
            }
        } catch (\Throwable $e) {
            // Safe fallback to site defaults
            $seoTitle = $siteName . ' - Là où les idées deviennent des projets';
            $seoDesc = $defaultDesc;
            $seoImg = $defaultImg;
        }
        $seoImgExt = strtolower(pathinfo(parse_url($seoImg, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
        $seoImgMime = match ($seoImgExt) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            default => 'image/jpeg',
        };
    @endphp

    <meta name="description" head-key="description" content="{{ $seoDesc }}">
    <meta name="keywords" head-key="keywords" content="{{ $settings->meta_keywords ?? 'tech237, startups, cameroun, tech, dev, ia, emplois, programmation' }}">
    <meta property="og:type" head-key="og:type" content="{{ $ogType }}">
    <meta property="og:site_name" head-key="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" head-key="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" head-key="og:description" content="{{ $seoDesc }}">
    <meta property="og:url" head-key="og:url" content="{{ url()->current() }}">
    <meta property="og:image" head-key="og:image" content="{{ $seoImg }}">
    <meta property="og:image:secure_url" head-key="og:image:secure_url" content="{{ $seoImg }}">
    <meta property="og:image:type" content="{{ $seoImgMime }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta itemprop="name" content="{{ $seoTitle }}">
    <meta itemprop="description" content="{{ $seoDesc }}">
    <meta itemprop="image" content="{{ $seoImg }}">
    <link rel="image_src" href="{{ $seoImg }}">
    @if(isset($portfolioUser) && $portfolioUser)
    <meta property="profile:username" content="{{ $portfolioUser->username }}">
    @endif
    <meta name="twitter:card" head-key="twitter:card" content="summary_large_image">
    <meta name="twitter:title" head-key="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" head-key="twitter:description" content="{{ $seoDesc }}">
    <meta name="twitter:image" head-key="twitter:image" content="{{ $seoImg }}">

    <!-- 100% Offline jQuery & Select2 -->
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>

    <!-- Instant Dark Mode initialization before paint -->
    <script>
        (function () {
            try {
                if (localStorage.getItem('tech237_dark_mode') === 'true') {
                    document.documentElement.classList.add('dark-mode');
                    document.documentElement.setAttribute('data-bs-theme', 'dark');
                }
            } catch (e) {}
        })();
    </script>

    @inertiaHead
    @vite(['resources/js/app.js'])
</head>
<body class="melty-body">
    @inertia

    <!-- 100% Offline Bootstrap JS Bundle -->
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
