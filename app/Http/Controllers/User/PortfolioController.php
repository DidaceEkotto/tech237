<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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

    public function uploadBg(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp,svg|max:10240',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'bg_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            
            $path = $file->storeAs('portfolio_bgs', $filename, 'public');

            // Mirror to public path for environments without symlink
            $publicFolder = public_path('storage/portfolio_bgs');
            if (!is_dir($publicFolder)) {
                @mkdir($publicFolder, 0777, true);
            }
            $storedPath = storage_path('app/public/' . $path);
            if (is_dir($publicFolder) && file_exists($storedPath)) {
                @copy($storedPath, $publicFolder . '/' . $filename);
            }

            return response()->json([
                'success' => true,
                'url' => asset('storage/' . $path),
                'path' => 'storage/' . $path,
            ]);
        }

        return response()->json(['error' => 'Échec du téléversement de l\'image de fond.'], 400);
    }
}
