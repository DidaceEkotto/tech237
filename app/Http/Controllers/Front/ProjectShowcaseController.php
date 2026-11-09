<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Startup;
use App\Models\Dispute;
use App\Models\UserMessage;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ProjectShowcaseController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type');
        $projects = Project::where('status', 'published')
            ->with(['user', 'startup', 'timelines'])
            ->when($type, fn($q) => $q->where('project_type', $type))
            ->latest()
            ->paginate(12);

        $user = Auth::guard('web')->user();
        $userUpvotedProjectIds = $user ? \App\Models\ProjectUpvote::where('user_id', $user->id)->pluck('project_id')->toArray() : [];

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
            'selectedType' => $type,
            'userUpvotedProjectIds' => $userUpvotedProjectIds,
        ]);
    }

    public function show(string $slug)
    {
        $project = Project::where('slug', $slug)
            ->with([
                'user',
                'startup',
                'timelines' => fn($q) => $q->where('status', 'approved')->with('user')->orderBy('event_date', 'desc'),
                'images' => fn($q) => $q->where('status', 'approved')->orderBy('order'),
                'programmingLanguages',
                'frameworksList',
                'activeCollaborators.user'
            ])
            ->firstOrFail();

        $project->increment('views_count');

        // Other projects from same founder or category
        $relatedProjects = Project::where('id', '!=', $project->id)
            ->where('project_type', $project->project_type)
            ->take(3)
            ->get();

        $user = Auth::guard('web')->user();
        $hasUpvoted = $user ? $project->hasUserUpvoted($user->id) : false;
        $isBookmarked = $user ? \App\Models\UserBookmark::where('user_id', $user->id)
            ->where('bookmarkable_type', 'project')
            ->where('bookmarkable_id', $project->id)
            ->exists() : false;

        return Inertia::render('Projects/Show', [
            'project' => $project,
            'relatedProjects' => $relatedProjects,
            'hasUpvoted' => $hasUpvoted,
            'isBookmarked' => $isBookmarked,
        ]);
    }

    public function report(Request $request)
    {
        $request->validate([
            'target_type' => 'required|string',
            'target_id' => 'required|integer',
            'reason' => 'required|string|max:150',
            'details' => 'nullable|string|max:1000',
        ]);

        Dispute::create([
            'user_id' => Auth::guard('web')->id(),
            'target_type' => $request->target_type,
            'target_id' => $request->target_id,
            'reason' => $request->reason,
            'details' => $request->details,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Votre signalement a bien été transmis à la modération.');
    }

    public function startupsIndex(Request $request)
    {
        $search = $request->query('search');
        $city = $request->query('city');

        $query = Startup::with(['user', 'projects' => fn($q) => $q->where('status', 'published')->latest()]);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($city) {
            $query->where('city', $city);
        }

        $startups = $query->latest()->paginate(12)->withQueryString();

        return Inertia::render('Startups/Index', [
            'startups' => $startups,
            'filters' => [
                'search' => $search,
                'city' => $city,
            ],
        ]);
    }

    public function showStartup(string $slug)
    {
        $startup = Startup::where('slug', $slug)
            ->orWhere('id', $slug)
            ->with(['user', 'projects.programmingLanguages', 'documents'])
            ->firstOrFail();

        $relatedStartups = Startup::where('id', '!=', $startup->id)
            ->where('city', $startup->city)
            ->take(3)
            ->get();

        return Inertia::render('Startups/Show', [
            'startup' => $startup,
            'relatedStartups' => $relatedStartups,
        ]);
    }

    public function contactStartup(Request $request, int $id)
    {
        $startup = Startup::with('user')->findOrFail($id);

        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'sujet' => 'required|string|max:200',
            'description' => 'required|string|max:5000',
        ]);

        $recipientEmail = $startup->user?->email;
        $recipientName = $startup->user?->name ?? $startup->name;

        if ($recipientEmail) {
            try {
                Mail::raw("Bonjour {$recipientName},\n\nVous avez reçu un nouveau message de contact concernant votre startup {$startup->name} sur Tech237.\n\nExpéditeur : {$validated['prenom']} {$validated['nom']} ({$validated['email']})\nSujet : {$validated['sujet']}\n\nMessage :\n{$validated['description']}\n\n---\nTech237 - Là où les idées deviennent des projets", function ($message) use ($recipientEmail, $startup, $validated) {
                    $message->to($recipientEmail)
                            ->replyTo($validated['email'], "{$validated['prenom']} {$validated['nom']}")
                            ->subject("[Tech237] Nouveau contact pour {$startup->name} : {$validated['sujet']}");
                });
            } catch (\Throwable $e) {
                Log::warning("Failed to send contact email to {$recipientEmail}: " . $e->getMessage());
            }
        }

        if ($startup->user_id) {
            UserMessage::create([
                'user_id' => $startup->user_id,
                'sender_id' => Auth::guard('web')->id(),
                'subject' => "[Contact {$startup->name}] " . $validated['sujet'],
                'message' => "Message de {$validated['prenom']} {$validated['nom']} ({$validated['email']}) :\n\n" . $validated['description'],
                'is_read' => false,
            ]);
        }

        return back()->with('success', 'Votre message a été transmis avec succès au fondateur de la startup !');
    }
}
