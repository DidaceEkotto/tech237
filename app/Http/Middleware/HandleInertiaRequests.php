<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\SiteSetting;
use App\Models\Category;
use App\Models\Rubrique;
use App\Models\Dossier;
use App\Models\AdBanner;
use Illuminate\Support\Facades\Auth;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     */
    public function share(Request $request): array
    {
        $settings = SiteSetting::current();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user('web') ? [
                    'id' => $request->user('web')->id,
                    'name' => $request->user('web')->name,
                    'first_name' => $request->user('web')->first_name,
                    'full_name' => $request->user('web')->full_name,
                    'email' => $request->user('web')->email,
                    'avatar' => $request->user('web')->avatar,
                    'status' => $request->user('web')->status,
                    'is_banned' => $request->user('web')->isBanned(),
                    'ban_reason' => $request->user('web')->ban_reason,
                    'xp' => $request->user('web')->xp ?? 0,
                    'rank_title' => $request->user('web')->rank_title,
                    'pending_level_up' => $request->user('web')->pending_level_up,
                ] : null,
            ],
            'siteSettings' => fn () => $settings,
            'navRubriques' => fn () => Rubrique::with(['categories' => fn($q) => $q->where('is_active', true)->orderBy('order')])
                ->where('is_active', true)
                ->orderBy('order')
                ->get(),
            'navCategories' => fn () => Category::with('children')
                ->whereNull('parent_id')
                ->where('is_active', true)
                ->orderBy('order')
                ->get(),
            'featuredDossiers' => fn () => Dossier::where('is_featured', true)->take(6)->get(),
            'topContributors' => fn () => \App\Models\User::where(function($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'banned');
            })->orderByDesc('xp')->take(3)->select('id', 'name', 'avatar', 'xp', 'rank_title')->get(),
            'adBanners' => fn () => AdBanner::where('is_active', true)
                ->inRandomOrder()
                ->get()
                ->groupBy('position')
                ->map(fn ($group) => [$group->first()]),
            'partners' => fn () => \Illuminate\Support\Facades\Schema::hasTable('partners') 
                ? \App\Models\Partner::active()->get() 
                : collect(),
            'currentLocale' => fn () => session('locale', 'fr'),
            'seo' => [
                'site_name' => $settings->site_name ?? 'Tech237',
                'title' => ($settings->site_name ?? 'Tech237') . ' - Là où les idées deviennent des projets | Tech, Startups & Innovations',
                'description' => $settings->meta_description ?? 'Tech237 - Là où les idées deviennent des projets. Le média tech, innovation et startups de référence au Cameroun.',
                'image' => $settings->seo_image ? asset($settings->seo_image) : asset('assets/images/logo.png'),
                'url' => $request->fullUrl(),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
