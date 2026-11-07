<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\JobOffer;
use App\Models\ForumCategory;
use App\Models\Conference;

class MeltyWysiwygForumJobApplyTest extends TestCase
{
    use RefreshDatabase;
    public function test_forum_categories_are_loaded_on_front()
    {
        $cat = ForumCategory::create([
            'name' => 'High-Tech & IA Test',
            'slug' => 'high-tech-ia-test',
            'description' => 'Discussions sur les IA',
            'icon' => 'bi-cpu',
            'color' => '#10B981',
            'order' => 1,
            'is_active' => true,
        ]);

        $response = $this->get('/forum');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Forum/Index')
                 ->has('categories')
        );

        $this->assertEquals('High-Tech & IA Test', $cat->name_fr);
    }

    public function test_job_apply_dedicated_page_loads_successfully()
    {
        $user = User::factory()->create();
        $job = JobOffer::create([
            'user_id' => $user->id,
            'title' => 'Développeur Fullstack Senior',
            'slug' => 'developpeur-fullstack-senior-test',
            'domain' => ['Développement Web & Mobile'],
            'contract_type' => 'CDI',
            'salary_type' => 'fixed',
            'salary_amount' => 750000,
            'city' => 'Douala',
            'accepts_cv' => true,
            'accepts_cover_letter' => true,
            'short_description' => 'Poste pour développeur expert Laravel / Vue.',
            'description' => 'Missions complètes et stack technique de pointe.',
            'deadline_date' => now()->addDays(20),
            'has_online_test' => false,
            'status' => 'approved',
        ]);

        $response = $this->get("/emploi/{$job->slug}/postuler");
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Jobs/Apply')
                 ->has('job')
                 ->where('job.title', 'Développeur Fullstack Senior')
        );

        $this->assertNotNull($job->formatted_deadline);
    }

    public function test_conference_creator_user_relationship()
    {
        $user = User::factory()->create();
        $conference = Conference::create([
            'user_id' => $user->id,
            'title_fr' => 'Conférence Tech Afrique 2026',
            'slug' => 'conf-tech-afrique-2026',
            'event_date' => now()->addDays(30),
            'location' => 'Salle Douala Bercy',
            'city' => 'Douala',
            'status' => 'approved',
        ]);

        $this->assertNotNull($conference->creatorUser);
        $this->assertEquals($user->id, $conference->creatorUser->id);
    }
}
