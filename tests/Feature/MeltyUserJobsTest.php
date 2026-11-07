<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\JobOffer;
use App\Models\JobQuestion;
use App\Models\JobApplication;
use Carbon\Carbon;

class MeltyUserJobsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->user = User::factory()->create([
            'email' => 'recruteur@test.com',
            'name' => 'Recruteur Pro',
        ]);
    }

    public function test_user_can_create_job_offer_without_online_test(): void
    {
        $payload = [
            'title' => 'Développeur Fullstack Laravel & Vue.js',
            'domains' => ['Développement Web & Mobile'],
            'contract_type' => 'CDI',
            'salary_type' => 'fixed',
            'salary_amount' => 45000,
            'city' => 'Douala',
            'deadline_date' => Carbon::now()->addDays(30)->toDateString(),
            'accepts_cv' => true,
            'accepts_cover_letter' => true,
            'short_description' => 'Recherche profil expérimenté pour concevoir nos plateformes.',
            'description' => '<p>Description complète avec éditeur wysiwyg.</p>',
            'has_online_test' => false,
        ];

        $response = $this->actingAs($this->user)->post('/espace-utilisateur/jobs', $payload);

        $response->assertRedirect('/espace-utilisateur?section=jobs');
        $this->assertDatabaseHas('job_offers', [
            'user_id' => $this->user->id,
            'title' => 'Développeur Fullstack Laravel & Vue.js',
            'status' => 'pending_validation',
            'has_online_test' => false,
            'city' => 'Douala',
        ]);
    }

    public function test_user_creating_job_offer_with_online_test_redirects_to_questions(): void
    {
        $payload = [
            'title' => 'Ingénieur DevOps & Cloud',
            'domains' => ['DevOps & Cloud Computing'],
            'contract_type' => 'CDI',
            'salary_type' => 'negotiable',
            'city' => 'Yaoundé',
            'deadline_date' => Carbon::now()->addDays(20)->toDateString(),
            'accepts_cv' => true,
            'accepts_cover_letter' => false,
            'short_description' => 'Supervision infra Docker et Kubernetes.',
            'description' => '<p>Missions Cloud.</p>',
            'has_online_test' => true,
        ];

        $response = $this->actingAs($this->user)->post('/espace-utilisateur/jobs', $payload);

        $offer = JobOffer::where('title', 'Ingénieur DevOps & Cloud')->first();
        $this->assertNotNull($offer);
        $response->assertRedirect('/espace-utilisateur/jobs/' . $offer->id . '/questions');

        // Now save questions
        $questionsPayload = [
            'questions' => [
                [
                    'question_text' => 'Qu\'est-ce qu\'un conteneur Docker ?',
                    'question_type' => 'text',
                    'options' => [],
                    'correct_answer' => null,
                ],
                [
                    'question_text' => 'Quel outil est un orchestrateur de conteneurs ?',
                    'question_type' => 'qcm',
                    'options' => ['Kubernetes', 'Photoshop', 'Excel', 'VLC'],
                    'correct_answer' => 'Kubernetes',
                ],
            ]
        ];

        $qResponse = $this->actingAs($this->user)->post('/espace-utilisateur/jobs/' . $offer->id . '/questions', $questionsPayload);
        $qResponse->assertRedirect('/espace-utilisateur?section=jobs');

        $this->assertEquals(2, $offer->questions()->count());
        $this->assertEquals('pending_validation', $offer->fresh()->status);
    }

    public function test_candidate_can_apply_with_cv_and_presentation(): void
    {
        $offer = JobOffer::create([
            'user_id' => $this->user->id,
            'title' => 'Développeur PHP Backend',
            'domain' => ['Développement Web & Mobile'],
            'contract_type' => 'CDI',
            'salary_type' => 'negotiable',
            'city' => 'Paris',
            'deadline_date' => Carbon::now()->addDays(15)->toDateString(),
            'accepts_cv' => true,
            'accepts_cover_letter' => true,
            'short_description' => 'Poste backend',
            'description' => '<p>Détails du poste</p>',
            'has_online_test' => true,
            'status' => 'approved',
        ]);

        $q = JobQuestion::create([
            'job_offer_id' => $offer->id,
            'question_text' => 'Quelle version de PHP supporte les fibers ?',
            'question_type' => 'qcm',
            'options' => ['PHP 5.6', 'PHP 7.4', 'PHP 8.1'],
            'correct_answer' => 'PHP 8.1',
            'order' => 1,
        ]);

        $cv = UploadedFile::fake()->create('mon_cv.pdf', 300, 'application/pdf');

        $applyPayload = [
            'name' => 'Alain Mbarga',
            'email' => 'alain@test.com',
            'phone' => '0612345678',
            'cv' => $cv,
            'presentation_text' => 'Je suis un développeur passionné par Laravel depuis plus de 4 ans.',
            'answers' => [
                $q->id => 'PHP 8.1',
            ],
        ];

        $response = $this->post('/emploi/' . $offer->id . '/postuler', $applyPayload);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('job_applications', [
            'job_offer_id' => $offer->id,
            'name' => 'Alain Mbarga',
            'email' => 'alain@test.com',
            'test_score' => 100,
        ]);
    }

    public function test_cannot_apply_when_deadline_passed(): void
    {
        $offer = JobOffer::create([
            'user_id' => $this->user->id,
            'title' => 'Poste Expiré',
            'domain' => ['Développement Web & Mobile'],
            'contract_type' => 'CDI',
            'salary_type' => 'negotiable',
            'city' => 'Paris',
            'deadline_date' => Carbon::now()->subDays(2)->toDateString(), // expired
            'accepts_cv' => true,
            'short_description' => 'Expiré',
            'description' => '<p>Expiré</p>',
            'status' => 'approved',
        ]);

        $cv = UploadedFile::fake()->create('cv.pdf', 200, 'application/pdf');

        $response = $this->post('/emploi/' . $offer->id . '/postuler', [
            'name' => 'Paul Test',
            'email' => 'paul@test.com',
            'phone' => '0600000000',
            'cv' => $cv,
            'presentation_text' => 'Présentation',
        ]);

        $response->assertSessionHasErrors('error');
    }

    public function test_recruiter_must_examine_test_before_scheduling_interview(): void
    {
        $offer = JobOffer::create([
            'user_id' => $this->user->id,
            'title' => 'Expert CyberSécurité',
            'domain' => ['Cybersécurité & Systèmes'],
            'contract_type' => 'CDI',
            'salary_type' => 'negotiable',
            'city' => 'Douala',
            'deadline_date' => Carbon::now()->addDays(10)->toDateString(),
            'accepts_cv' => true,
            'has_online_test' => true,
            'status' => 'approved',
        ]);

        $application = JobApplication::create([
            'job_offer_id' => $offer->id,
            'name' => 'Sophie Martin',
            'email' => 'sophie@test.com',
            'phone' => '0622334455',
            'cv_file' => 'cvs/test.pdf',
            'presentation_text' => 'Spécialiste pentest',
            'test_answers' => ['Q1' => 'Réponse'],
            'test_score' => 1,
            'status' => 'submitted',
            'examined_at' => null, // Not yet examined!
        ]);

        // Attempting to schedule interview before examining test should be blocked
        $futureDate = Carbon::now()->addDays(5)->toDateString();
        $resBlock = $this->actingAs($this->user)->post("/espace-utilisateur/jobs/candidats/{$application->id}/convoquer", [
            'interview_date' => $futureDate,
            'interview_time' => '14:30',
            'location' => 'Siège Melty - 12 Rue de la Paix',
            'notes' => 'Convocation entretien',
        ]);

        $resBlock->assertSessionHasErrors('error');
        $this->assertEquals('submitted', $application->fresh()->status);

        // Now examine test
        $resExamine = $this->actingAs($this->user)->post("/espace-utilisateur/jobs/candidats/{$application->id}/examiner");
        $resExamine->assertSessionHas('success');
        $this->assertNotNull($application->fresh()->examined_at);

        // Attempting with past date/time should be blocked
        $pastDate = Carbon::now()->subDays(1)->toDateString();
        $resPast = $this->actingAs($this->user)->post("/espace-utilisateur/jobs/candidats/{$application->id}/convoquer", [
            'interview_date' => $pastDate,
            'interview_time' => '10:00',
            'location' => 'Bureau',
            'notes' => 'Test',
        ]);
        $resPast->assertSessionHasErrors('interview_date');

        // Now schedule with valid future date and time
        $resSuccess = $this->actingAs($this->user)->post("/espace-utilisateur/jobs/candidats/{$application->id}/convoquer", [
            'interview_date' => $futureDate,
            'interview_time' => '15:00',
            'location' => 'Siège Melty - Salle A',
            'notes' => 'Nous vous confirmons votre convocation pour un entretien physique.',
        ]);

        $resSuccess->assertSessionHas('success');
        $this->assertEquals('interview_scheduled', $application->fresh()->status);
        $this->assertDatabaseHas('job_interviews', [
            'job_application_id' => $application->id,
            'interview_time' => '15:00',
            'location' => 'Siège Melty - Salle A',
        ]);
    }

    public function test_recruiter_can_reject_applicant_with_reason(): void
    {
        $offer = JobOffer::create([
            'user_id' => $this->user->id,
            'title' => 'Admin Système',
            'domain' => ['Réseaux & Télécommunications'],
            'contract_type' => 'CDD',
            'salary_type' => 'negotiable',
            'city' => 'Douala',
            'deadline_date' => Carbon::now()->addDays(5)->toDateString(),
            'accepts_cv' => true,
            'status' => 'approved',
        ]);

        $application = JobApplication::create([
            'job_offer_id' => $offer->id,
            'name' => 'Marc Dupont',
            'email' => 'marc@test.com',
            'phone' => '0633445566',
            'cv_file' => 'cvs/test.pdf',
            'presentation_text' => 'Profil junior',
            'status' => 'submitted',
        ]);

        $res = $this->actingAs($this->user)->post("/espace-utilisateur/jobs/candidats/{$application->id}/rejeter", [
            'rejection_reason' => 'Profil ne correspondant pas au niveau de séniorité requis.',
        ]);

        $res->assertSessionHas('success');
        $this->assertEquals('rejected', $application->fresh()->status);
        $this->assertEquals('Profil ne correspondant pas au niveau de séniorité requis.', $application->fresh()->rejection_reason);
    }
}
