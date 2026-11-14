<?php

namespace App\Services\Portfolio;

use App\Models\PortfolioPage;
use App\Models\PortfolioPageRevision;
use App\Models\PortfolioSite;
use App\Models\PortfolioThemePart;
use App\Models\User;
use Illuminate\Support\Str;

class SitePublisherService
{
    public function __construct(private readonly PageTreeValidator $validator)
    {
    }

    public function ensurePrimarySiteForUser(User $user): PortfolioSite
    {
        $site = PortfolioSite::where('user_id', $user->id)
            ->where('is_primary', true)
            ->first();

        if ($site) {
            return $site;
        }

        $baseSlug = Str::slug($user->username ?: ($user->name ?: 'site-' . $user->id));
        if ($baseSlug === '') {
            $baseSlug = 'site-' . $user->id;
        }
        $slug = $baseSlug;
        $counter = 1;
        while (PortfolioSite::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $site = PortfolioSite::create([
            'user_id' => $user->id,
            'name' => ($user->name ?: $user->username) . ' — Site Portfolio',
            'slug' => $slug,
            'is_primary' => true,
            'is_published' => (bool) $user->portfolio_published,
            'global_settings' => [
                'accent_color' => $user->portfolio_layout['accent_color'] ?? '#00A859',
                'font' => $user->portfolio_layout['font'] ?? 'poppins',
                'theme' => $user->portfolio_layout['theme'] ?? 'emerald',
                'bg_color' => '#F8FAFC',
                'text_color' => '#0F172A',
            ],
            'published_at' => $user->portfolio_published ? now() : null,
        ]);

        // Create default Homepage with starter Hero + About + Contact sections
        $starterTree = $this->buildStarterHomeTree($user);

        PortfolioPage::create([
            'portfolio_site_id' => $site->id,
            'title' => 'Accueil',
            'slug' => 'accueil',
            'is_homepage' => true,
            'draft_tree' => $starterTree,
            'published_tree' => $user->portfolio_published ? $starterTree : null,
            'status' => $user->portfolio_published ? 'published' : 'draft',
            'sort_order' => 0,
        ]);

        // Create default Header & Footer in Theme Builder
        PortfolioThemePart::create([
            'portfolio_site_id' => $site->id,
            'type' => 'header',
            'name' => 'Header Principal',
            'is_active' => true,
            'display_conditions' => ['include' => ['all']],
            'tree' => [
                'position' => 'sticky',
                'font_size' => '13px',
                'border_radius' => '9999px',
                'bg_type' => 'glass',
                'bg_color' => '#FFFFFF',
                'text_color' => '#0F172A',
                'logo_url' => '',
                'cta_text' => 'Me contacter',
                'cta_target' => '#contact',
                'menu_items' => [
                    ['id' => 'm_home', 'label' => 'Accueil', 'icon' => 'bi-house-door-fill', 'target' => '#hero', 'children' => []],
                    ['id' => 'm_about', 'label' => 'À propos', 'icon' => 'bi-person-lines-fill', 'target' => '#about', 'children' => []],
                    ['id' => 'm_contact', 'label' => 'Contact', 'icon' => 'bi-envelope-fill', 'target' => '#contact', 'children' => []],
                ],
            ],
        ]);

        PortfolioThemePart::create([
            'portfolio_site_id' => $site->id,
            'type' => 'footer',
            'name' => 'Footer Principal',
            'is_active' => true,
            'display_conditions' => ['include' => ['all']],
            'tree' => [
                'layout' => 'multi_column',
                'copyright' => '© ' . date('Y') . ' ' . ($user->name ?: $user->username) . ' — Propulsé par Tech237',
                'show_socials' => true,
                'show_back_to_top' => true,
            ],
        ]);

        return $site;
    }

    public function savePageDraft(PortfolioPage $page, User $user, array $rawTree, string $label = 'Sauvegarde'): PortfolioPage
    {
        $cleanTree = $this->validator->sanitizeTree($rawTree);
        $page->update([
            'draft_tree' => $cleanTree,
        ]);

        PortfolioPageRevision::create([
            'portfolio_page_id' => $page->id,
            'user_id' => $user->id,
            'tree_snapshot' => $cleanTree,
            'label' => $label,
        ]);

        // Keep last 25 revisions per page
        $keepIds = $page->revisions()->take(25)->pluck('id');
        if ($keepIds->isNotEmpty()) {
            $page->revisions()->whereNotIn('id', $keepIds)->delete();
        }

        return $page->fresh();
    }

    public function publishSite(PortfolioSite $site): PortfolioSite
    {
        foreach ($site->pages as $page) {
            $page->update([
                'published_tree' => $page->draft_tree ?? $this->validator->emptyTree(),
                'status' => 'published',
            ]);
        }

        $site->update([
            'is_published' => true,
            'published_at' => now(),
        ]);

        if ($site->is_primary && $site->user) {
            $site->user->update(['portfolio_published' => true]);
        }

        return $site->fresh();
    }

    public function buildStarterHomeTree(User $user): array
    {
        return [
            'schema_version' => PageTreeValidator::SCHEMA_VERSION,
            'sections' => [
                [
                    'id' => 'sec_hero',
                    'label' => 'Hero / En-tête',
                    'design' => ['padding_top' => 32, 'padding_bottom' => 32, 'bg_color' => 'transparent'],
                    'advanced' => ['html_id' => 'hero', 'animation' => 'fade_up'],
                    'rows' => [
                        [
                            'id' => 'row_hero',
                            'layout' => '1',
                            'design' => [],
                            'advanced' => [],
                            'columns' => [
                                [
                                    'id' => 'col_hero',
                                    'width' => '12',
                                    'design' => [],
                                    'advanced' => [],
                                    'modules' => [
                                        [
                                            'id' => 'mod_hero',
                                            'type' => 'hero',
                                            'template_variant' => 'impact_split',
                                            'content' => [
                                                'badge' => $user->rank_title ?: 'Profil Tech237',
                                                'title' => $user->name ?: $user->username,
                                                'subtitle' => $user->headline ?: 'Développeur & Créateur Digital',
                                                'description' => strip_tags((string) ($user->bio ?? 'Bienvenue sur mon site portfolio professionnel.')),
                                                'cta_primary_text' => 'Me contacter',
                                                'cta_primary_url' => '#contact',
                                                'cta_secondary_text' => 'Voir mes projets',
                                                'cta_secondary_url' => '#projects',
                                                'image_url' => $user->avatar ?: '',
                                            ],
                                            'design' => ['accent_color' => '#00A859'],
                                            'advanced' => [],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'id' => 'sec_contact',
                    'label' => 'Contact',
                    'design' => ['padding_top' => 24, 'padding_bottom' => 32],
                    'advanced' => ['html_id' => 'contact', 'animation' => 'fade_up'],
                    'rows' => [
                        [
                            'id' => 'row_contact',
                            'layout' => '1',
                            'design' => [],
                            'advanced' => [],
                            'columns' => [
                                [
                                    'id' => 'col_contact',
                                    'width' => '12',
                                    'design' => [],
                                    'advanced' => [],
                                    'modules' => [
                                        [
                                            'id' => 'mod_contact',
                                            'type' => 'contact',
                                            'template_variant' => 'split_left',
                                            'content' => [
                                                'title' => 'Entrons en contact',
                                                'subtitle' => 'Un projet ou une opportunité ? Écrivez-moi directement.',
                                                'button_text' => 'Envoyer le message',
                                            ],
                                            'design' => [],
                                            'advanced' => [],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
