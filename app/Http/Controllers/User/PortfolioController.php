<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PortfolioController extends Controller
{
    public function save(Request $request)
    {
        $user = Auth::guard('web')->user();

        $validated = $request->validate([
            'username' => [
                'required',
                'string',
                'min:3',
                'max:60',
                'regex:/^[a-zA-Z0-9_\-\.]+$/',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'headline' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:150',
            'availability' => 'nullable|string|max:50',
            'bio' => 'nullable|string|max:3000',
            'skills' => 'nullable|array',
            'social_links' => 'nullable|array',
            'experiences' => 'nullable|array',
            'educations' => 'nullable|array',
            'custom_sections' => 'nullable|array',
            'portfolio_layout' => 'nullable|array',
            'portfolio_published' => 'nullable|boolean',
        ], [
            'username.required' => 'Le pseudo (username) pour votre lien public est obligatoire.',
            'username.regex' => 'Le pseudo ne peut contenir que des lettres, chiffres, tirets et underscores (sans espaces).',
            'username.unique' => 'Ce pseudo est déjà réservé par un autre utilisateur. Choisissez-en un autre.',
        ]);

        // Clean username: lowercase and trim
        $validated['username'] = Str::lower(trim($validated['username']));
        if (isset($validated['portfolio_published'])) {
            $validated['portfolio_published'] = (bool)$validated['portfolio_published'];
        }

        $user->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Votre portfolio et vos préférences ont été enregistrés avec succès !',
                'user' => $user->fresh(),
                'portfolio_url' => url('/@' . $user->username),
            ]);
        }

        return back()->with('success', 'Votre portfolio a bien été mis à jour.');
    }
}
