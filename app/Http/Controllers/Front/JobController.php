<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\JobOffer;
use App\Models\JobApplication;
use App\Models\JobQuestion;
use Illuminate\Support\Str;

class JobController extends Controller
{
    public function index(Request $request)
    {
        $domain = $request->input('domain');
        $contract = $request->input('contract');
        $city = $request->input('city');
        $search = $request->input('search');

        $query = JobOffer::whereIn('status', ['approved', 'published'])->latest();

        if ($domain) {
            $query->whereJsonContains('domain', $domain);
        }

        if ($contract) {
            $query->where('contract_type', $contract);
        }

        if ($city) {
            $query->where('city', 'like', "%{$city}%");
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        $jobs = $query->paginate(12)->withQueryString();

        $domains = [
            'Développement Web & Mobile',
            'Réseaux & Télécommunications',
            'Cybersécurité & Systèmes',
            'Data Science & Intelligence Artificielle',
            'DevOps & Cloud Computing',
            'UI/UX Design & Gestion de Produit',
            'Support & Maintenance Informatique',
            'Gestion de Projet IT & Méthodes Agiles',
        ];

        $contracts = [
            'CDI',
            'CDD',
            'Stage de vacances professionnel',
            'Stage pré-emploi',
            'Freelance / Prestation',
            'Alternance'
        ];

        return Inertia::render('Jobs/Index', [
            'jobs' => $jobs,
            'domains' => $domains,
            'contracts' => $contracts,
            'filters' => [
                'domain' => $domain,
                'contract' => $contract,
                'city' => $city,
                'search' => $search,
            ],
        ]);
    }

    public function show(string $slug, Request $request)
    {
        $job = JobOffer::with(['questions', 'user'])
            ->where(function($q) use ($slug) {
                $q->where('slug', $slug);
                if (is_numeric($slug)) {
                    $q->orWhere('id', (int) $slug);
                }
            })
            ->whereIn('status', ['approved', 'published'])
            ->firstOrFail();

        $userId = $request->user('web')?->id;
        $hasApplied = false;
        if ($userId) {
            $hasApplied = JobApplication::where('job_offer_id', $job->id)
                ->where('user_id', $userId)
                ->exists();
        }

        $similarJobs = JobOffer::whereIn('status', ['approved', 'published'])
            ->where('id', '!=', $job->id)
            ->take(3)
            ->get();

        return Inertia::render('Jobs/Show', [
            'job' => $job,
            'hasApplied' => $hasApplied,
            'similarJobs' => $similarJobs,
        ]);
    }

    public function applyForm(string $slug, Request $request)
    {
        $job = JobOffer::with(['questions', 'user'])
            ->where(function($q) use ($slug) {
                $q->where('slug', $slug);
                if (is_numeric($slug)) {
                    $q->orWhere('id', (int) $slug);
                }
            })
            ->whereIn('status', ['approved', 'published'])
            ->firstOrFail();

        $userId = $request->user('web')?->id;
        $hasApplied = false;
        if ($userId) {
            $hasApplied = JobApplication::where('job_offer_id', $job->id)
                ->where('user_id', $userId)
                ->exists();
        }

        return Inertia::render('Jobs/Apply', [
            'job' => $job,
            'hasApplied' => $hasApplied,
        ]);
    }

    public function checkAppliedEmail(int $id, Request $request)
    {
        $email = trim(strtolower((string) $request->input('email', '')));
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['applied' => false]);
        }

        $exists = JobApplication::where('job_offer_id', $id)
            ->where('email', $email)
            ->exists();

        return response()->json(['applied' => $exists]);
    }

    public function apply(Request $request, int $id)
    {
        $job = JobOffer::with('questions')->findOrFail($id);

        if ($job->deadline_date && $job->deadline_date->isPast() && !$job->deadline_date->isToday()) {
            return back()->withErrors(['error' => 'La date limite de dépôt des candidatures pour cette offre est dépassée.']);
        }

        $email = trim(strtolower((string) $request->input('email', '')));
        $userId = $request->user('web')?->id;

        $alreadyApplied = JobApplication::where('job_offer_id', $job->id)
            ->where(function($q) use ($userId, $email) {
                if ($userId) {
                    $q->where('user_id', $userId);
                }
                if ($email) {
                    $q->orWhere('email', $email);
                }
            })
            ->exists();

        if ($alreadyApplied) {
            return back()->withErrors(['email' => 'Vous avez déjà postulé à cette offre d\'emploi avec cette adresse email. Une seule candidature par personne est autorisée.']);
        }

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'presentation_text' => 'required|string',
            'cv' => 'required|file|mimes:pdf,doc,docx|max:10240',
            'cover_letter_file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ];

        $validated = $request->validate($rules, [
            'name.required' => 'Votre nom complet est obligatoire.',
            'email.required' => 'Votre adresse email est obligatoire.',
            'cv.required' => 'Le dépôt de votre CV au format Word (.doc, .docx) ou PDF est obligatoire.',
            'cv.mimes' => 'Le CV doit obligatoirement être un document Word (.doc, .docx) ou PDF (.pdf).',
            'cover_letter_file.mimes' => 'La lettre de motivation doit être un document Word (.doc, .docx) ou PDF (.pdf).',
            'presentation_text.required' => 'Le texte de présentation "Parlez-nous un peu de vous" est obligatoire.',
        ]);

        // Save CV & Cover Letter using ImageService
        $cvPath = null;
        if ($request->hasFile('cv')) {
            $cvPath = \App\Services\ImageService::saveImage($request->file('cv'), 'cv');
        }

        $coverLetterPath = null;
        if ($request->hasFile('cover_letter_file')) {
            $coverLetterPath = \App\Services\ImageService::saveImage($request->file('cover_letter_file'), 'cl');
        }

        $userId = $request->user('web')?->id;

        // Process online test answers if present
        $answers = $request->input('answers', []);
        $score = null;
        if ($job->has_online_test && count($job->questions) > 0 && is_array($answers)) {
            $correct = 0;
            $qcmCount = 0;
            foreach ($job->questions as $q) {
                if ($q->question_type === 'qcm' && $q->correct_answer) {
                    $qcmCount++;
                    if (isset($answers[$q->id]) && trim((string)$answers[$q->id]) === trim((string)$q->correct_answer)) {
                        $correct++;
                    }
                }
            }
            if ($qcmCount > 0) {
                $score = round(($correct / $qcmCount) * 100);
            }
        }

        JobApplication::create([
            'job_offer_id' => $job->id,
            'user_id' => $userId,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'cv_file' => $cvPath,
            'cover_letter_file' => $coverLetterPath,
            'presentation_text' => $validated['presentation_text'],
            'test_answers' => !empty($answers) ? $answers : null,
            'test_score' => $score,
            'status' => 'submitted',
        ]);

        return redirect()->route('jobs.show', $job->slug)->with('success', 'Votre candidature a été transmise avec succès au recruteur.');
    }
}
