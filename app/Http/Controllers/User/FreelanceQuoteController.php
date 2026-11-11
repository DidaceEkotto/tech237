<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\FreelanceQuote;
use App\Models\FreelanceSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;

class FreelanceQuoteController extends Controller
{
    /**
     * Sauvegarde des paramètres du simulateur de TJM / rentabilité
     */
    public function saveSettings(Request $request)
    {
        $user = Auth::guard('web')->user();

        $validated = $request->validate([
            'target_monthly_net' => 'required|numeric|min:0',
            'work_weeks_per_year' => 'required|numeric|min:1|max:52',
            'work_days_per_week' => 'required|numeric|min:1|max:7',
            'non_billable_percent' => 'required|numeric|min:0|max:90',
            'tax_percent' => 'required|numeric|min:0|max:50',
            'expenses' => 'nullable|array',
            'computed_tjm' => 'required|numeric|min:0',
            'computed_thm' => 'required|numeric|min:0',
            'payment_info' => 'nullable|array',
        ]);

        $setting = FreelanceSetting::updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        return response()->json([
            'success' => true,
            'message' => 'Paramètres du calculateur enregistrés avec succès.',
            'setting' => $setting,
        ]);
    }

    /**
     * Création d'un nouveau devis ou facture
     */
    public function storeQuote(Request $request)
    {
        $user = Auth::guard('web')->user();

        $validated = $request->validate([
            'type' => 'required|in:quote,invoice',
            'quote_number' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'client_name' => 'required|string|max:150',
            'client_company' => 'nullable|string|max:150',
            'client_email' => 'nullable|email|max:150',
            'client_phone' => 'nullable|string|max:50',
            'client_address' => 'nullable|string|max:255',
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date',
            'logo_url' => 'nullable|string|max:500',
            'currency' => 'nullable|string|max:10',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.unit' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.total' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'payment_milestones' => 'nullable|array',
            'payment_methods' => 'nullable|array',
            'notes' => 'nullable|string|max:3000',
            'terms' => 'nullable|string|max:3000',
            'status' => 'nullable|in:draft,sent,accepted,rejected,paid',
        ]);

        $validated['user_id'] = $user->id;
        $validated['uuid'] = (string) Str::uuid();
        $validated['currency'] = $validated['currency'] ?: 'XAF';
        $validated['status'] = $validated['status'] ?: 'draft';

        $quote = FreelanceQuote::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Devis créé avec succès.',
            'quote' => $quote,
        ]);
    }

    /**
     * Mise à jour d'un devis existant
     */
    public function updateQuote(Request $request, $id)
    {
        $user = Auth::guard('web')->user();
        $quote = FreelanceQuote::where('user_id', $user->id)->findOrFail($id);

        $validated = $request->validate([
            'type' => 'required|in:quote,invoice',
            'quote_number' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'client_name' => 'required|string|max:150',
            'client_company' => 'nullable|string|max:150',
            'client_email' => 'nullable|email|max:150',
            'client_phone' => 'nullable|string|max:50',
            'client_address' => 'nullable|string|max:255',
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date',
            'logo_url' => 'nullable|string|max:500',
            'currency' => 'nullable|string|max:10',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.unit' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.total' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'payment_milestones' => 'nullable|array',
            'payment_methods' => 'nullable|array',
            'notes' => 'nullable|string|max:3000',
            'terms' => 'nullable|string|max:3000',
            'status' => 'nullable|in:draft,sent,accepted,rejected,paid',
        ]);

        $quote->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Devis mis à jour avec succès.',
            'quote' => $quote->fresh(),
        ]);
    }

    /**
     * Suppression d'un devis
     */
    public function deleteQuote($id)
    {
        $user = Auth::guard('web')->user();
        $quote = FreelanceQuote::where('user_id', $user->id)->findOrFail($id);
        $quote->delete();

        return response()->json([
            'success' => true,
            'message' => 'Devis supprimé avec succès.',
        ]);
    }

    /**
     * Duplication d'un devis
     */
    public function duplicateQuote($id)
    {
        $user = Auth::guard('web')->user();
        $quote = FreelanceQuote::where('user_id', $user->id)->findOrFail($id);

        $newQuote = $quote->replicate();
        $newQuote->uuid = (string) Str::uuid();
        $newQuote->quote_number = $quote->quote_number . '-COPIE';
        $newQuote->status = 'draft';
        $newQuote->issue_date = now()->format('Y-m-d');
        $newQuote->due_date = now()->addDays(30)->format('Y-m-d');
        $newQuote->accepted_at = null;
        $newQuote->paid_at = null;
        $newQuote->save();

        return response()->json([
            'success' => true,
            'message' => 'Devis dupliqué avec succès.',
            'quote' => $newQuote,
        ]);
    }

    /**
     * Changement rapide de statut (ex: marquer comme accepté, payé, envoyé)
     */
    public function updateQuoteStatus(Request $request, $id)
    {
        $user = Auth::guard('web')->user();
        $quote = FreelanceQuote::where('user_id', $user->id)->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:draft,sent,accepted,rejected,paid',
        ]);

        $updateData = ['status' => $validated['status']];
        if ($validated['status'] === 'accepted' && !$quote->accepted_at) {
            $updateData['accepted_at'] = now();
        } elseif ($validated['status'] === 'paid' && !$quote->paid_at) {
            $updateData['paid_at'] = now();
        }

        $quote->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Statut du devis actualisé.',
            'quote' => $quote->fresh(),
        ]);
    }

    /**
     * Consultation publique d'un devis par le client via son UUID
     */
    public function showPublic($uuid)
    {
        $quote = FreelanceQuote::where('uuid', $uuid)->with('user')->firstOrFail();

        // Récupération des infos freelance de l'émetteur
        $freelanceUser = $quote->user;
        $freelanceSetting = FreelanceSetting::where('user_id', $freelanceUser->id)->first();

        return Inertia::render('Quote/PublicShow', [
            'quote' => $quote,
            'freelance' => [
                'name' => $freelanceUser->name,
                'username' => $freelanceUser->username,
                'avatar' => $freelanceUser->avatar ? asset($freelanceUser->avatar) : asset('assets/images/default-avatar.png'),
                'email' => $freelanceUser->email,
                'phone' => $freelanceUser->phone,
                'headline' => $freelanceUser->headline ?: 'Développeur & Consultant Tech',
                'location' => $freelanceUser->location ?: 'Cameroun',
            ],
            'freelanceSetting' => $freelanceSetting,
        ]);
    }

    /**
     * Acceptation publique du devis par le client en 1 clic
     */
    public function acceptPublic(Request $request, $uuid)
    {
        $quote = FreelanceQuote::where('uuid', $uuid)->firstOrFail();

        if ($quote->status === 'paid') {
            return response()->json([
                'error' => 'Ce devis a déjà été réglé.',
            ], 422);
        }

        $quote->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Merci ! Vous avez accepté ce devis. Le prestataire en a été informé.',
            'quote' => $quote->fresh(),
        ]);
    }

    /**
     * Téléversement du logo du freelance pour ses devis / factures
     */
    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|file|image|max:10240',
        ]);

        try {
            $file = $request->file('logo');
            $extension = strtolower($file->getClientOriginalExtension() ?: 'png');
            $filename = 'logo_' . time() . '_' . Str::random(8) . '.' . $extension;

            $storageFolder = storage_path('app/public/freelance_logos');
            if (!is_dir($storageFolder)) {
                @mkdir($storageFolder, 0777, true);
            }
            $publicFolder = public_path('storage/freelance_logos');
            if (!is_dir($publicFolder)) {
                @mkdir($publicFolder, 0777, true);
            }

            $path = $file->storeAs('freelance_logos', $filename, 'public');

            $storedPath = storage_path('app/public/' . $path);
            if (is_dir($publicFolder) && file_exists($storedPath)) {
                @copy($storedPath, $publicFolder . '/' . $filename);
            }

            return response()->json([
                'success' => true,
                'url' => asset('storage/' . $path),
                'path' => 'storage/' . $path,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Erreur lors du téléversement du logo : ' . $e->getMessage()], 422);
        }
    }
}
