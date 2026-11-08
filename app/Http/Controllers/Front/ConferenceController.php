<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use App\Models\Conference;
use App\Models\ConferenceRegistration;
use App\Models\SiteSetting;
use App\Services\ImageService;

class ConferenceController extends Controller
{
    public function index(Request $request)
    {
        $city = $request->input('city');
        $date = $request->input('date');
        $search = $request->input('search');

        $query = Conference::where('status', 'approved')
            ->withCount('registrations')
            ->orderBy('event_date', 'asc');

        if ($city) {
            $query->where('city', 'like', "%{$city}%");
        }

        if ($date) {
            $query->whereDate('event_date', '>=', $date);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('title_fr', 'like', "%{$search}%")
                  ->orWhere('title_en', 'like', "%{$search}%")
                  ->orWhere('description_fr', 'like', "%{$search}%")
                  ->orWhere('description_en', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        $conferences = $query->paginate(9)->withQueryString();

        $cities = Conference::where('status', 'approved')->distinct()->pluck('city');

        return Inertia::render('Conferences/Index', [
            'conferences' => $conferences,
            'cities' => $cities,
            'filters' => [
                'city' => $city,
                'date' => $date,
                'search' => $search,
            ],
        ]);
    }

    public function show(string $slug, Request $request)
    {
        $conference = Conference::with(['creatorUser', 'admin'])
            ->withCount('registrations')
            ->where('slug', $slug)
            ->firstOrFail();

        $userId = $request->user('web')?->id;
        $isUserRegistered = false;
        if ($userId) {
            $isUserRegistered = ConferenceRegistration::where('conference_id', $conference->id)
                ->where('user_id', $userId)
                ->exists();
        }

        $upcomingConferences = Conference::where('status', 'approved')
            ->where('id', '!=', $conference->id)
            ->where('event_date', '>=', now()->toDateString())
            ->orderBy('event_date', 'asc')
            ->take(3)
            ->get();

        return Inertia::render('Conferences/Show', [
            'conference' => $conference,
            'isUserRegistered' => $isUserRegistered,
            'upcomingConferences' => $upcomingConferences,
        ]);
    }

    public function register(Request $request, int $id)
    {
        $conference = Conference::findOrFail($id);

        if (!$conference->accepts_registrations) {
            return back()->with('error', 'Les inscriptions ne sont pas ouvertes pour cet événement.');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'phone_whatsapp' => 'nullable|string|max:50',
            'gender' => 'required|in:Homme,Femme,Autre',
            'city' => 'required|string|max:100',
            'bio' => 'nullable|string|max:500',
            'photo' => 'nullable|image|max:5120',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = ImageService::saveImage($request->file('photo'), 'conference_attendee');
        }

        $userId = $request->user('web')?->id;

        // Check if already registered
        $existing = ConferenceRegistration::where('conference_id', $conference->id)
            ->where('email', $validated['email'])
            ->first();

        if ($existing) {
            return back()->with('error', 'Vous êtes déjà inscrit(e) à cet événement avec cette adresse email.');
        }

        $fullName = trim($validated['first_name'] . ' ' . $validated['last_name']);

        $registration = ConferenceRegistration::create([
            'conference_id' => $conference->id,
            'user_id' => $userId,
            'name' => $validated['last_name'] ?: $fullName,
            'first_name' => $validated['first_name'],
            'email' => $validated['email'],
            'phone_whatsapp' => $validated['phone_whatsapp'] ?? null,
            'gender' => $validated['gender'],
            'city' => $validated['city'],
            'photo' => $photoPath,
            'bio' => $validated['bio'] ?? null,
            'is_confirmed' => true,
            'confirmed_at' => now(),
        ]);

        // Send confirmation email with invitation
        try {
            $settings = SiteSetting::current();
            Mail::send('emails.conference_confirmation', [
                'conference' => $conference,
                'registration' => $registration,
                'settings' => $settings,
            ], function ($message) use ($registration, $conference) {
                $recipientName = trim($registration->first_name . ' ' . $registration->name);
                $message->to($registration->email, $recipientName)
                    ->subject("Confirmation officielle de votre inscription - {$conference->title_fr}");
            });
        } catch (\Throwable $e) {
            Log::error('Erreur envoi email confirmation inscription conference: ' . $e->getMessage());
        }

        if (Schema::hasColumn('conferences', 'current_attendees_count')) {
            $conference->increment('current_attendees_count');
        }

        return back()->with('success', 'Félicitations ! Votre inscription a été validée avec succès. Un email de confirmation avec votre billet officiel vous a été transmis.');
    }

    public function propose(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'start_date' => 'required|date|after:now',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'city' => 'required|string|max:100',
            'venue_address' => 'nullable|string|max:255',
            'location_type' => 'required|in:in_person,online,hybrid',
            'max_attendees' => 'required|integer|min:10',
            'description' => 'required|string',
            'banner_image' => 'nullable|image|max:10240',
        ]);

        $bannerPath = null;
        if ($request->hasFile('banner_image')) {
            $bannerPath = ImageService::saveImage($request->file('banner_image'), 'conference');
        }

        $slug = Str::slug($validated['title']) . '-' . Str::random(5);

        Conference::create([
            'user_id' => $request->user('web')->id,
            'title_fr' => $validated['title'],
            'slug' => $slug,
            'cover_image' => $bannerPath,
            'event_date' => substr($validated['start_date'], 0, 10),
            'end_date' => !empty($validated['end_date']) ? substr($validated['end_date'], 0, 10) : substr($validated['start_date'], 0, 10),
            'location' => $validated['venue_address'] ?? null,
            'city' => $validated['city'],
            'is_in_person' => $validated['location_type'] !== 'online',
            'is_free' => true,
            'description_fr' => $validated['description'],
            'accepts_registrations' => true,
            'status' => 'pending_validation',
            'is_official_admin' => false,
        ]);

        return back()->with('success', 'Votre événement a été soumis et sera validé par l\'équipe Tech237 sous 24h.');
    }
}
