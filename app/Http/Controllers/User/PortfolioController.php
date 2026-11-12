<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:60',
            'headline' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:150',
            'availability' => 'nullable|string|max:50',
            'bio' => 'nullable|string|max:15000',
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
        $file = $request->file('image') 
            ?: $request->file('bg') 
            ?: $request->file('file') 
            ?: (count($request->allFiles()) > 0 ? array_values($request->allFiles())[0] : null);
        if (!$file) {
            return response()->json(['error' => 'Aucun fichier image trouvé. Veuillez sélectionner une image (PNG, JPG, WebP).'], 422);
        }

        $validator = \Illuminate\Support\Facades\Validator::make(['image' => $file], [
            'image' => 'required|file|max:15360',
        ], [
            'image.required' => 'Veuillez sélectionner un fichier image valide.',
            'image.file' => 'Le fichier sélectionné n\'est pas un fichier valide.',
            'image.max' => 'L\'image d\'arrière-plan ne doit pas dépasser 15 Mo.',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        try {
            $extension = strtolower($file->getClientOriginalExtension() ?: 'png');
            $filename = 'bg_' . time() . '_' . Str::random(8) . '.' . $extension;

            // Ensure destination directories exist
            $storageFolder = storage_path('app/public/portfolio_bgs');
            if (!is_dir($storageFolder)) {
                @mkdir($storageFolder, 0777, true);
            }
            $publicFolder = public_path('storage/portfolio_bgs');
            if (!is_dir($publicFolder)) {
                @mkdir($publicFolder, 0777, true);
            }

            $path = $file->storeAs('portfolio_bgs', $filename, 'public');

            $storedPath = storage_path('app/public/' . $path);
            if (is_dir($publicFolder) && file_exists($storedPath)) {
                @copy($storedPath, $publicFolder . '/' . $filename);
            }

            return response()->json([
                'success' => true,
                'url' => asset('storage/' . $path),
                'relative_url' => '/storage/' . $path,
                'path' => 'storage/' . $path,
            ]);
        } catch (\Throwable $e) {
            Log::error('Portfolio bg upload failed: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors du téléversement : ' . $e->getMessage()], 422);
        }
    }

    public function uploadCv(Request $request)
    {
        $file = $request->file('cv') ?: $request->file('file');
        if (!$file) {
            return response()->json(['error' => 'Aucun fichier CV trouvé. Veuillez sélectionner un document PDF ou Word.'], 422);
        }

        $validator = \Illuminate\Support\Facades\Validator::make(['cv' => $file], [
            'cv' => 'required|file|mimes:pdf,doc,docx,odt,rtf|max:20480',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        try {
            $originalName = $file->getClientOriginalName();
            $extension = strtolower($file->getClientOriginalExtension() ?: 'pdf');
            $filename = 'cv_' . time() . '_' . Str::random(8) . '.' . $extension;

            $storageFolder = storage_path('app/public/portfolio_cvs');
            if (!is_dir($storageFolder)) {
                @mkdir($storageFolder, 0777, true);
            }
            $publicFolder = public_path('storage/portfolio_cvs');
            if (!is_dir($publicFolder)) {
                @mkdir($publicFolder, 0777, true);
            }

            $path = $file->storeAs('portfolio_cvs', $filename, 'public');

            $storedPath = storage_path('app/public/' . $path);
            if (is_dir($publicFolder) && file_exists($storedPath)) {
                @copy($storedPath, $publicFolder . '/' . $filename);
            }

            return response()->json([
                'success' => true,
                'url' => asset('storage/' . $path),
                'relative_url' => '/storage/' . $path,
                'path' => 'storage/' . $path,
                'filename' => $originalName,
            ]);
        } catch (\Throwable $e) {
            Log::error('Portfolio cv upload failed: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors du téléversement du CV : ' . $e->getMessage()], 422);
        }
    }

    public function uploadSlide(Request $request)
    {
        $file = $request->file('image') 
            ?: $request->file('slide') 
            ?: $request->file('file') 
            ?: (count($request->allFiles()) > 0 ? array_values($request->allFiles())[0] : null);

        // Fallback: Check if base64 data string was provided
        $base64Data = $request->input('image_base64') ?: $request->input('image') ?: $request->input('slide');
        if (!$file && is_string($base64Data) && preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
            $data = substr($base64Data, strpos($base64Data, ',') + 1);
            $data = base64_decode($data);
            if ($data !== false) {
                $ext = strtolower($type[1]);
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'])) {
                    $ext = 'png';
                }
                $filename = 'slide_' . time() . '_' . Str::random(8) . '.' . $ext;
                $storageFolder = storage_path('app/public/portfolio_slides');
                if (!is_dir($storageFolder)) {
                    @mkdir($storageFolder, 0777, true);
                }
                $publicFolder = public_path('storage/portfolio_slides');
                if (!is_dir($publicFolder)) {
                    @mkdir($publicFolder, 0777, true);
                }
                file_put_contents($storageFolder . '/' . $filename, $data);
                if (is_dir($publicFolder)) {
                    @copy($storageFolder . '/' . $filename, $publicFolder . '/' . $filename);
                }
                $path = 'portfolio_slides/' . $filename;
                return response()->json([
                    'success' => true,
                    'url' => asset('storage/' . $path),
                    'relative_url' => '/storage/' . $path,
                    'path' => 'storage/' . $path,
                ]);
            }
        }

        if (!$file) {
            return response()->json(['error' => 'Aucun fichier image trouvé. Veuillez sélectionner une image (PNG, JPG, WebP).'], 422);
        }

        $validator = \Illuminate\Support\Facades\Validator::make(['image' => $file], [
            'image' => 'required|file|max:15360',
        ], [
            'image.required' => 'Veuillez sélectionner un fichier image valide.',
            'image.file' => 'Le fichier sélectionné n\'est pas un fichier valide.',
            'image.max' => 'L\'image de la diapositive ne doit pas dépasser 15 Mo.',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        try {
            $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            $filename = 'slide_' . time() . '_' . Str::random(8) . '.' . $extension;

            $storageFolder = storage_path('app/public/portfolio_slides');
            if (!is_dir($storageFolder)) {
                @mkdir($storageFolder, 0777, true);
            }
            $publicFolder = public_path('storage/portfolio_slides');
            if (!is_dir($publicFolder)) {
                @mkdir($publicFolder, 0777, true);
            }

            $path = $file->storeAs('portfolio_slides', $filename, 'public');

            $storedPath = storage_path('app/public/' . $path);
            if (is_dir($publicFolder) && file_exists($storedPath)) {
                @copy($storedPath, $publicFolder . '/' . $filename);
            }

            return response()->json([
                'success' => true,
                'url' => asset('storage/' . $path),
                'relative_url' => '/storage/' . $path,
                'path' => 'storage/' . $path,
            ]);
        } catch (\Throwable $e) {
            Log::error('Portfolio slide upload failed: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors du téléversement du slide : ' . $e->getMessage()], 422);
        }
    }

    public function uploadPreloaderImage(Request $request)
    {
        $file = $request->file('image') 
            ?: $request->file('preloader') 
            ?: $request->file('file') 
            ?: (count($request->allFiles()) > 0 ? array_values($request->allFiles())[0] : null);
        if (!$file) {
            return response()->json(['error' => 'Aucun fichier image trouvé. Veuillez sélectionner une image (PNG, JPG, WebP).'], 422);
        }

        $validator = \Illuminate\Support\Facades\Validator::make(['image' => $file], [
            'image' => 'required|file|max:10240',
        ], [
            'image.required' => 'Veuillez sélectionner un fichier image valide.',
            'image.file' => 'Le fichier sélectionné n\'est pas un fichier valide.',
            'image.max' => 'L\'image de préchargement ne doit pas dépasser 10 Mo.',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        try {
            $extension = strtolower($file->getClientOriginalExtension() ?: 'png');
            $filename = 'preloader_' . time() . '_' . Str::random(8) . '.' . $extension;

            $storageFolder = storage_path('app/public/portfolio_preloaders');
            if (!is_dir($storageFolder)) {
                @mkdir($storageFolder, 0777, true);
            }
            $publicFolder = public_path('storage/portfolio_preloaders');
            if (!is_dir($publicFolder)) {
                @mkdir($publicFolder, 0777, true);
            }

            $path = $file->storeAs('portfolio_preloaders', $filename, 'public');

            $storedPath = storage_path('app/public/' . $path);
            if (is_dir($publicFolder) && file_exists($storedPath)) {
                @copy($storedPath, $publicFolder . '/' . $filename);
            }

            return response()->json([
                'success' => true,
                'url' => asset('storage/' . $path),
                'relative_url' => '/storage/' . $path,
                'path' => 'storage/' . $path,
            ]);
        } catch (\Throwable $e) {
            Log::error('Portfolio preloader upload failed: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors du téléversement du preloader : ' . $e->getMessage()], 422);
        }
    }

    public function submitContact(Request $request, $username)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => 'required|email|max:150',
            'subject' => 'required|string|max:200',
            'message' => 'required|string|max:5000',
        ], [
            'first_name.required' => 'Le prénom est obligatoire.',
            'email.required' => 'Votre adresse email est obligatoire.',
            'email.email' => 'Veuillez saisir une adresse email valide.',
            'subject.required' => 'Le sujet de votre message est obligatoire.',
            'message.required' => 'Le contenu du message est obligatoire.',
        ]);

        $portfolioOwner = User::where('username', $username)->first();
        if (!$portfolioOwner) {
            return response()->json(['error' => 'Ce portfolio est introuvable.'], 404);
        }

        $senderFullName = trim($validated['first_name'] . ' ' . ($validated['last_name'] ?? ''));
        $senderEmail = $validated['email'];
        $subject = $validated['subject'];
        $body = $validated['message'];

        // 1. Email to Portfolio Owner
        try {
            if ($portfolioOwner->email) {
                Mail::raw(
                    "Bonjour {$portfolioOwner->name},\n\nVous avez reçu un nouveau message depuis votre portfolio Tech237 :\n\n" .
                    "Expéditeur : {$senderFullName} ({$senderEmail})\n" .
                    "Sujet : {$subject}\n\n" .
                    "Message :\n{$body}\n\n" .
                    "--------------------------------------------------\n" .
                    "Vous pouvez répondre directement en écrivant à {$senderEmail}.\n" .
                    "L'équipe Tech237 - https://tech237.net",
                    function ($m) use ($portfolioOwner, $senderEmail, $senderFullName, $subject) {
                        $m->to($portfolioOwner->email, $portfolioOwner->name)
                          ->replyTo($senderEmail, $senderFullName)
                          ->subject("[Tech237 Portfolio] {$subject} (de {$senderFullName})");
                    }
                );
            }
        } catch (\Throwable $e) {
            Log::warning("Could not send contact email to owner {$portfolioOwner->email}: " . $e->getMessage());
        }

        // 2. Confirmation Email to Visitor
        try {
            Mail::raw(
                "Bonjour {$senderFullName},\n\n" .
                "Nous vous confirmons que votre message destiné à {$portfolioOwner->name} a bien été transmis avec succès via son portfolio officiel sur Tech237.\n\n" .
                "Récapitulatif de votre message :\n" .
                "Sujet : {$subject}\n\n" .
                "\"{$body}\"\n\n" .
                "{$portfolioOwner->name} prendra contact avec vous à l'adresse {$senderEmail}.\n\n" .
                "Cordialement,\n" .
                "L'équipe Tech237 • Là où les idées deviennent des projets au Cameroun\n" .
                "https://tech237.net",
                function ($m) use ($senderEmail, $senderFullName, $portfolioOwner) {
                    $m->to($senderEmail, $senderFullName)
                      ->subject("Accusé de réception : Votre message à {$portfolioOwner->name} a été envoyé • Tech237");
                }
            );
        } catch (\Throwable $e) {
            Log::warning("Could not send contact confirmation email to visitor {$senderEmail}: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => "Merci {$senderFullName} ! Votre message a bien été envoyé à {$portfolioOwner->name}. Un accusé de réception vous a été adressé par email.",
        ]);
    }
}
