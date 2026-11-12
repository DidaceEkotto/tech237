<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;

class FormBuilderController extends Controller
{
    /**
     * Display a listing of the user's forms.
     */
    public function index()
    {
        $user = Auth::guard('web')->user();

        $forms = Form::where('user_id', $user->id)
            ->withCount('responses')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($form) {
                return [
                    'id' => $form->id,
                    'uuid' => $form->uuid,
                    'title' => $form->title,
                    'slug' => $form->slug,
                    'public_url' => $form->public_url,
                    'description' => $form->description,
                    'display_mode' => $form->display_mode,
                    'status' => $form->status,
                    'theme_color' => $form->theme_color,
                    'views_count' => $form->views_count,
                    'responses_count' => $form->responses_count,
                    'max_responses' => $form->max_responses,
                    'expires_at' => $form->expires_at ? $form->expires_at->toIso8601String() : null,
                    'is_expired' => $form->isExpired(),
                    'is_quota_reached' => $form->isQuotaReached(),
                    'created_at' => $form->created_at->translatedFormat('d M Y'),
                ];
            });

        return Inertia::render('User/Forms/Index', [
            'forms' => $forms,
        ]);
    }

    /**
     * Show the form for creating a new form.
     */
    public function create()
    {
        return Inertia::render('User/Forms/Create');
    }

    /**
     * Store a newly created form in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::guard('web')->user();

        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:2000',
            'display_mode' => 'required|in:classic,conversation',
            'theme_color' => 'nullable|string|max:20',
            'template' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $form = Form::create([
                'user_id' => $user->id,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'display_mode' => $validated['display_mode'],
                'theme_color' => $validated['theme_color'] ?? '#00A859',
                'status' => 'draft',
                'notify_email_on_response' => true,
                'notification_email' => $user->email,
            ]);

            // Default fields according to template or starter
            $template = $request->input('template', 'blank');
            $this->seedInitialFields($form, $template);

            DB::commit();

            return redirect()->route('user.forms.edit', $form->id)
                ->with('success', 'Votre formulaire a été créé avec succès ! Personnalisez-le ci-dessous.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Form creation failed: ' . $e->getMessage());
            return back()->withErrors(['title' => 'Erreur lors de la création du formulaire : ' . $e->getMessage()]);
        }
    }

    /**
     * Show the editor / builder for the specified form.
     */
    public function edit($id)
    {
        $user = Auth::guard('web')->user();
        $form = Form::where('user_id', $user->id)
            ->with(['fields' => function ($q) {
                $q->orderBy('order', 'asc');
            }])
            ->findOrFail($id);

        return Inertia::render('User/Forms/Edit', [
            'form' => [
                'id' => $form->id,
                'uuid' => $form->uuid,
                'title' => $form->title,
                'slug' => $form->slug,
                'public_url' => $form->public_url,
                'description' => $form->description,
                'display_mode' => $form->display_mode,
                'cover_image' => $form->cover_image,
                'theme_color' => $form->theme_color ?: '#00A859',
                'status' => $form->status,
                'requires_login' => (bool) $form->requires_login,
                'one_response_per_user' => (bool) $form->one_response_per_user,
                'max_responses' => $form->max_responses,
                'expires_at' => $form->expires_at ? $form->expires_at->format('Y-m-d\TH:i') : '',
                'notify_email_on_response' => (bool) $form->notify_email_on_response,
                'notification_email' => $form->notification_email ?: $user->email,
                'send_respondent_confirmation' => (bool) $form->send_respondent_confirmation,
                'confirmation_message' => $form->confirmation_message,
                'redirect_url' => $form->redirect_url,
                'fields' => $form->fields->map(function ($f) {
                    return [
                        'id' => $f->id,
                        'label' => $f->label,
                        'name' => $f->name,
                        'type' => $f->type,
                        'placeholder' => $f->placeholder,
                        'help_text' => $f->help_text,
                        'is_required' => (bool) $f->is_required,
                        'options' => $f->options ?: [],
                        'order' => $f->order,
                        'conditional_logic' => $f->conditional_logic ?: null,
                        'config' => $f->config ?: null,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Update the form settings and synchronize all fields.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::guard('web')->user();
        $form = Form::where('user_id', $user->id)->findOrFail($id);

        // A published form is locked from further modifications to protect responses integrity
        if ($form->status === 'published') {
            $msg = 'Ce formulaire a déjà été mis en ligne et ne peut plus être modifié afin de préserver l\'intégrité des réponses et questions. Pour apporter des modifications, vous pouvez le dupliquer.';
            if ($request->wantsJson()) {
                return response()->json(['error' => $msg], 422);
            }
            return back()->withErrors(['title' => $msg]);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'slug' => 'required|string|max:200|regex:/^[a-zA-Z0-9_\-]+$/|unique:forms,slug,' . $form->id,
            'description' => 'nullable|string|max:3000',
            'display_mode' => 'required|in:classic,conversation',
            'cover_image' => 'nullable|string|max:255',
            'theme_color' => 'nullable|string|max:25',
            'status' => 'required|in:published,draft,closed',
            'requires_login' => 'nullable|boolean',
            'one_response_per_user' => 'nullable|boolean',
            'max_responses' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'notify_email_on_response' => 'nullable|boolean',
            'notification_email' => 'nullable|email|max:150',
            'send_respondent_confirmation' => 'nullable|boolean',
            'confirmation_message' => 'nullable|string|max:1500',
            'redirect_url' => 'nullable|url|max:255',
            'fields' => 'required|array|min:1',
            'fields.*.label' => 'required|string|max:255',
            'fields.*.type' => 'required|string',
            'fields.*.is_required' => 'nullable|boolean',
            'fields.*.placeholder' => 'nullable|string|max:255',
            'fields.*.help_text' => 'nullable|string|max:500',
            'fields.*.options' => 'nullable|array',
            'fields.*.conditional_logic' => 'nullable|array',
            'fields.*.config' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            $form->update([
                'title' => $validated['title'],
                'slug' => Str::slug($validated['slug']),
                'description' => $validated['description'] ?? null,
                'display_mode' => $validated['display_mode'],
                'cover_image' => $validated['cover_image'] ?? null,
                'theme_color' => $validated['theme_color'] ?? '#00A859',
                'status' => $validated['status'],
                'requires_login' => !empty($validated['requires_login']),
                'one_response_per_user' => !empty($validated['one_response_per_user']),
                'max_responses' => $validated['max_responses'] ?? null,
                'expires_at' => !empty($validated['expires_at']) ? $validated['expires_at'] : null,
                'notify_email_on_response' => !empty($validated['notify_email_on_response']),
                'notification_email' => $validated['notification_email'] ?? $user->email,
                'send_respondent_confirmation' => !empty($validated['send_respondent_confirmation']),
                'confirmation_message' => $validated['confirmation_message'] ?? null,
                'redirect_url' => $validated['redirect_url'] ?? null,
            ]);

            // Sync fields
            $incomingIds = [];
            foreach ($request->input('fields', []) as $index => $fieldData) {
                $fieldId = $fieldData['id'] ?? null;
                $slugName = 'field_' . ($index + 1) . '_' . Str::slug(Str::limit($fieldData['label'], 20, ''));

                $payload = [
                    'label' => $fieldData['label'],
                    'name' => $slugName,
                    'type' => $fieldData['type'],
                    'placeholder' => $fieldData['placeholder'] ?? null,
                    'help_text' => $fieldData['help_text'] ?? null,
                    'is_required' => !empty($fieldData['is_required']),
                    'options' => $fieldData['options'] ?? null,
                    'order' => $index,
                    'conditional_logic' => $fieldData['conditional_logic'] ?? null,
                    'config' => $fieldData['config'] ?? null,
                ];

                if ($fieldId && FormField::where('id', $fieldId)->where('form_id', $form->id)->exists()) {
                    FormField::where('id', $fieldId)->update($payload);
                    $incomingIds[] = $fieldId;
                } else {
                    $newField = $form->fields()->create($payload);
                    $incomingIds[] = $newField->id;
                }
            }

            // Remove deleted fields
            $form->fields()->whereNotIn('id', $incomingIds)->delete();

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Formulaire enregistré avec succès !',
                    'public_url' => $form->public_url,
                ]);
            }

            return back()->with('success', 'Formulaire enregistré avec succès !');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Form update failed: ' . $e->getMessage());
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
            return back()->withErrors(['title' => 'Erreur lors de l\'enregistrement : ' . $e->getMessage()]);
        }
    }

    /**
     * Delete the specified form.
     */
    public function destroy($id)
    {
        $user = Auth::guard('web')->user();
        $form = Form::where('user_id', $user->id)->findOrFail($id);
        $form->delete();

        return redirect()->route('user.forms.index')
            ->with('success', 'Formulaire supprimé avec succès.');
    }

    /**
     * Duplicate an existing form.
     */
    public function duplicate($id)
    {
        $user = Auth::guard('web')->user();
        $source = Form::where('user_id', $user->id)->with('fields')->findOrFail($id);

        DB::beginTransaction();
        try {
            $newForm = $source->replicate([
                'uuid',
                'slug',
                'views_count',
            ]);
            $newForm->title = $source->title . ' (Copie)';
            $newForm->status = 'draft';
            $newForm->save();

            foreach ($source->fields as $field) {
                $newField = $field->replicate(['form_id']);
                $newField->form_id = $newForm->id;
                $newField->save();
            }

            DB::commit();

            return redirect()->route('user.forms.edit', $newForm->id)
                ->with('success', 'Formulaire dupliqué avec succès !');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la duplication : ' . $e->getMessage());
        }
    }

    /**
     * Toggle status between published and closed.
     */
    public function toggleStatus(Request $request, $id)
    {
        $user = Auth::guard('web')->user();
        $form = Form::where('user_id', $user->id)->findOrFail($id);

        $newStatus = $request->input('status');
        if (!in_array($newStatus, ['published', 'closed'])) {
            return back()->with('error', 'Statut invalide.');
        }

        $form->status = $newStatus;
        $form->save();

        $msg = $newStatus === 'published' ? 'Formulaire remis en ligne avec succès !' : 'Formulaire fermé aux nouvelles réponses.';
        return back()->with('success', $msg);
    }

    /**
     * Display responses, statistics & charts for a form.
     */
    public function responses($id)
    {
        $user = Auth::guard('web')->user();
        $form = Form::where('user_id', $user->id)
            ->with(['fields' => function ($q) {
                $q->orderBy('order', 'asc');
            }])
            ->findOrFail($id);

        $responses = FormResponse::where('form_id', $form->id)
            ->with('user:id,name,email,username')
            ->latest()
            ->paginate(30);

        $totalResponses = FormResponse::where('form_id', $form->id)->count();
        $avgSeconds = FormResponse::where('form_id', $form->id)->avg('completed_in_seconds');

        // Build aggregate stats for select/radio/checkbox/rating fields
        $fieldsStats = [];
        foreach ($form->fields as $field) {
            if (in_array($field->type, ['select', 'radio', 'checkbox', 'rating', 'city_cameroon'])) {
                $counts = [];
                foreach ($field->options ?: [] as $opt) {
                    $counts[$opt] = 0;
                }

                $rawAnswers = FormResponse::where('form_id', $form->id)->pluck('answers');
                foreach ($rawAnswers as $ans) {
                    $val = $ans[$field->id] ?? $ans[$field->name] ?? null;
                    if (is_array($val)) {
                        foreach ($val as $subVal) {
                            $counts[$subVal] = ($counts[$subVal] ?? 0) + 1;
                        }
                    } elseif ($val !== null && $val !== '') {
                        $counts[$val] = ($counts[$val] ?? 0) + 1;
                    }
                }

                $fieldsStats[] = [
                    'field_id' => $field->id,
                    'label' => $field->label,
                    'type' => $field->type,
                    'counts' => $counts,
                ];
            }
        }

        return Inertia::render('User/Forms/Responses', [
            'form' => [
                'id' => $form->id,
                'title' => $form->title,
                'slug' => $form->slug,
                'public_url' => $form->public_url,
                'views_count' => $form->views_count,
                'theme_color' => $form->theme_color ?: '#00A859',
                'status' => $form->status,
                'fields' => $form->fields,
            ],
            'responses' => $responses,
            'summary' => [
                'total_responses' => $totalResponses,
                'views_count' => $form->views_count,
                'conversion_rate' => $form->views_count > 0 ? round(($totalResponses / $form->views_count) * 100, 1) : 0,
                'avg_duration_seconds' => $avgSeconds ? round($avgSeconds) : null,
            ],
            'fieldsStats' => $fieldsStats,
        ]);
    }

    /**
     * Export all form responses to CSV.
     */
    public function exportCsv($id)
    {
        $user = Auth::guard('web')->user();
        $form = Form::where('user_id', $user->id)
            ->with(['fields' => function ($q) {
                $q->orderBy('order', 'asc');
            }])
            ->findOrFail($id);

        $responses = FormResponse::where('form_id', $form->id)->latest()->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="reponses_' . $form->slug . '_' . date('Y-m-d') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($form, $responses) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM so Excel opens accents correctly
            fputs($handle, "\xEF\xBB\xBF");

            // Header Row
            $cols = ['# ID', 'Date & Heure', 'Email Répondant', 'Durée (sec)'];
            foreach ($form->fields as $f) {
                $cols[] = $f->label;
            }
            fputcsv($handle, $cols, ';');

            // Data Rows
            foreach ($responses as $idx => $resp) {
                $row = [
                    $idx + 1,
                    $resp->created_at->format('Y-m-d H:i:s'),
                    $resp->respondent_email ?: 'Anonyme',
                    $resp->completed_in_seconds ?: '--',
                ];

                foreach ($form->fields as $f) {
                    $val = $resp->answers[$f->id] ?? $resp->answers[$f->name] ?? '';
                    if (is_array($val)) {
                        $val = implode(', ', $val);
                    }
                    $row[] = (string) $val;
                }

                fputcsv($handle, $row, ';');
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * User guide for Tech237 Forms (including QR Code, Conditional Logic, Field Types, Email Alerts).
     */
    public function guide()
    {
        return Inertia::render('User/Forms/Guide');
    }

    /**
     * Helper to seed starter fields according to chosen template.
     */
    private function seedInitialFields(Form $form, string $template)
    {
        if ($template === 'event_registration') {
            $form->fields()->createMany([
                ['label' => 'Nom complet', 'name' => 'field_1_nom', 'type' => 'text', 'placeholder' => 'ex: Christian Kamga', 'is_required' => true, 'order' => 0],
                ['label' => 'Adresse email', 'name' => 'field_2_email', 'type' => 'email', 'placeholder' => 'ex: contact@exemple.cm', 'is_required' => true, 'order' => 1],
                ['label' => 'Numéro WhatsApp / Téléphone', 'name' => 'field_3_phone', 'type' => 'phone', 'placeholder' => '+237 6...', 'is_required' => true, 'order' => 2],
                ['label' => 'Ville de résidence', 'name' => 'field_4_ville', 'type' => 'city_cameroon', 'is_required' => true, 'order' => 3],
                ['label' => 'Votre niveau ou rôle principal', 'name' => 'field_5_role', 'type' => 'select', 'options' => ['Étudiant en informatique', 'Développeur Junior', 'Développeur Confirmé / Senior', 'Lead Dev / CTO', 'Designer / Product Manager', 'Autre passionné tech'], 'is_required' => true, 'order' => 4],
            ]);
        } elseif ($template === 'tech_recruitment') {
            $form->fields()->createMany([
                ['label' => 'Nom et Prénom', 'name' => 'field_1_nom', 'type' => 'text', 'placeholder' => 'Votre nom', 'is_required' => true, 'order' => 0],
                ['label' => 'Email professionnel', 'name' => 'field_2_email', 'type' => 'email', 'placeholder' => 'email@domaine.cm', 'is_required' => true, 'order' => 1],
                ['label' => 'Lien Profil GitHub / GitLab', 'name' => 'field_3_github', 'type' => 'github_link', 'placeholder' => 'https://github.com/votre-compte', 'is_required' => true, 'order' => 2],
                ['label' => 'Votre spécialité technique', 'name' => 'field_4_spec', 'type' => 'radio', 'options' => ['Fullstack (Laravel + Vue/React)', 'Mobile (Flutter / React Native)', 'Backend API (PHP, Node, Go, Python)', 'DevOps & Cloud'], 'is_required' => true, 'order' => 3],
                ['label' => 'Extrait de code / Solution technique (Snippet)', 'name' => 'field_5_snippet', 'type' => 'code_snippet', 'placeholder' => '// Collez ici votre algorithme ou snippet propre...', 'help_text' => 'Montrez votre style de code et vos bonnes pratiques.', 'is_required' => false, 'order' => 4],
                ['label' => 'Téléverser votre CV (PDF)', 'name' => 'field_6_cv', 'type' => 'file', 'help_text' => 'Format PDF, max 10 Mo', 'is_required' => true, 'order' => 5],
            ]);
        } elseif ($template === 'feedback_survey') {
            $form->fields()->createMany([
                ['label' => 'Votre nom ou pseudo (optionnel)', 'name' => 'field_1_nom', 'type' => 'text', 'is_required' => false, 'order' => 0],
                ['label' => 'Note globale de votre expérience', 'name' => 'field_2_note', 'type' => 'rating', 'is_required' => true, 'order' => 1],
                ['label' => 'Recommanderiez-vous notre solution à vos confrères ?', 'name' => 'field_3_recom', 'type' => 'radio', 'options' => ['Certainement oui', 'Probablement', 'Non'], 'is_required' => true, 'order' => 2],
                ['label' => 'Quels aspects pouvons-nous améliorer ?', 'name' => 'field_4_remarques', 'type' => 'textarea', 'placeholder' => 'Partagez vos impressions détaillées...', 'is_required' => false, 'order' => 3],
            ]);
        } else {
            // Blank default fields
            $form->fields()->createMany([
                ['label' => 'Votre nom complet', 'name' => 'field_1_nom', 'type' => 'text', 'placeholder' => 'ex: Alex Song', 'is_required' => true, 'order' => 0],
                ['label' => 'Votre email', 'name' => 'field_2_email', 'type' => 'email', 'placeholder' => 'alex@domaine.cm', 'is_required' => true, 'order' => 1],
                ['label' => 'Votre message ou réponse', 'name' => 'field_3_message', 'type' => 'textarea', 'placeholder' => 'Écrivez ici...', 'is_required' => true, 'order' => 2],
            ]);
        }
    }
}
