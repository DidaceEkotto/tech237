<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Mail\NewsletterWelcomeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\NewsletterSubscriber;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $subscriber = NewsletterSubscriber::firstOrCreate(
            ['email' => strtolower($request->email)],
            [
                'ip_address' => $request->ip() ?: '127.0.0.1',
                'subscribed_at' => now(),
                'is_active' => true,
            ]
        );

        try {
            Mail::to($subscriber->email)->send(new NewsletterWelcomeMail($subscriber));
        } catch (\Throwable $e) {
            Log::warning('Newsletter welcome mail failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Merci ! Vous êtes bien inscrit à la newsletter Tech237. / Thank you! You are subscribed to the Tech237 newsletter.',
        ]);
    }
}
