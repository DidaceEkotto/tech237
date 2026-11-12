<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PublicFormController extends Controller
{
    /**
     * Display the public form.
     */
    public function show($slug)
    {
        $form = Form::where('slug', $slug)
            ->orWhere('uuid', $slug)
            ->with(['user:id,name,username,avatar', 'fields' => function ($q) {
                $q->orderBy('order', 'asc');
            }])
            ->firstOrFail();

        // Increment views count (atomic)
        $form->increment('views_count');

        $user = Auth::guard('web')->user();

        // Check if user has already submitted (if 1 response per user is enabled)
        $alreadySubmitted = false;
        if ($form->one_response_per_user) {
            if ($user) {
                $alreadySubmitted = FormResponse::where('form_id', $form->id)
                    ->where('user_id', $user->id)
                    ->exists();
            } else {
                // If login is not strictly required but 1 response per user is set, check IP or session
                $clientIp = request()->ip();
                $alreadySubmitted = FormResponse::where('form_id', $form->id)
                    ->where('ip_address', $clientIp)
                    ->exists();
            }
        }

        $isClosed = $form->status === 'closed' || $form->status === 'draft';
        $isExpired = $form->isExpired();
        $isQuotaReached = $form->isQuotaReached();

        return Inertia::render('Forms/PublicShow', [
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
                'expires_at' => $form->expires_at ? $form->expires_at->toIso8601String() : null,
                'confirmation_message' => $form->confirmation_message,
                'redirect_url' => $form->redirect_url,
                'user' => [
                    'name' => $form->user->name,
                    'username' => $form->user->username,
                    'avatar' => $form->user->avatar,
                ],
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
            'gate' => [
                'is_closed' => $isClosed,
                'is_expired' => $isExpired,
                'is_quota_reached' => $isQuotaReached,
                'requires_login' => $form->requires_login && !$user,
                'already_submitted' => $alreadySubmitted,
                'current_user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ] : null,
            ],
        ]);
    }

    /**
     * Submit responses to the form.
     */
    public function submit(Request $request, $slug)
    {
        $form = Form::where('slug', $slug)
            ->orWhere('uuid', $slug)
            ->with(['user', 'fields' => function ($q) {
                $q->orderBy('order', 'asc');
            }])
            ->firstOrFail();

        $user = Auth::guard('web')->user();

        // 1. Check permissions & status
        if ($form->status !== 'published') {
            return response()->json(['error' => 'Ce formulaire n\'accepte plus de réponses (statut désactivé).'], 403);
        }

        if ($form->isExpired()) {
            return response()->json(['error' => 'La date limite pour répondre à ce formulaire est dépassée.'], 403);
        }

        if ($form->isQuotaReached()) {
            return response()->json(['error' => 'Le nombre maximum de réponses autorisées pour ce formulaire a été atteint.'], 403);
        }

        if ($form->requires_login && !$user) {
            return response()->json(['error' => 'Vous devez être connecté à votre compte Tech237 pour répondre à ce formulaire.'], 401);
        }

        if ($form->one_response_per_user) {
            if ($user && FormResponse::where('form_id', $form->id)->where('user_id', $user->id)->exists()) {
                return response()->json(['error' => 'Vous avez déjà soumis votre réponse à ce formulaire.'], 422);
            }
            if (!$user && FormResponse::where('form_id', $form->id)->where('ip_address', $request->ip())->exists()) {
                return response()->json(['error' => 'Une réponse a déjà été enregistrée depuis votre adresse IP.'], 422);
            }
        }

        // 2. Validate answers
        $rawAnswers = $request->input('answers', []);
        if (is_string($rawAnswers)) {
            $rawAnswers = json_decode($rawAnswers, true) ?: [];
        }

        $processedAnswers = [];
        $respondentEmail = $user ? $user->email : null;

        foreach ($form->fields as $field) {
            $fieldKey = (string) $field->id;
            $val = $rawAnswers[$fieldKey] ?? $rawAnswers[$field->name] ?? null;

            // Handle file upload
            if ($field->type === 'file') {
                if ($request->hasFile("file_{$field->id}")) {
                    $uploadedFile = $request->file("file_{$field->id}");
                    $ext = strtolower($uploadedFile->getClientOriginalExtension());
                    $filename = 'form_' . $form->id . '_' . time() . '_' . Str::random(8) . '.' . $ext;
                    $path = $uploadedFile->storeAs('form_uploads', $filename, 'public');
                    $val = '/storage/' . $path;
                }
            }

            // Check if required
            if ($field->is_required && (is_null($val) || $val === '' || (is_array($val) && count($val) === 0))) {
                return response()->json([
                    'error' => "Le champ « {$field->label} » est obligatoire.",
                    'field_id' => $field->id,
                ], 422);
            }

            // Detect respondent email if provided in an email field
            if ($field->type === 'email' && !empty($val) && filter_var($val, FILTER_VALIDATE_EMAIL)) {
                $respondentEmail = $val;
            }

            $processedAnswers[$field->id] = $val;
        }

        // 3. Create response
        $completedSeconds = $request->input('completed_in_seconds', null);

        $formResponse = FormResponse::create([
            'form_id' => $form->id,
            'user_id' => $user ? $user->id : null,
            'respondent_email' => $respondentEmail,
            'answers' => $processedAnswers,
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit($request->userAgent(), 500),
            'completed_in_seconds' => is_numeric($completedSeconds) ? (int)$completedSeconds : null,
        ]);

        // 4. Send Email Notification to Form Creator (if enabled)
        if ($form->notify_email_on_response) {
            $destEmail = $form->notification_email ?: ($form->user ? $form->user->email : null);
            if ($destEmail && filter_var($destEmail, FILTER_VALIDATE_EMAIL)) {
                try {
                    Mail::to($destEmail)->send(new \App\Mail\FormNewResponseMail($form, $formResponse));
                } catch (\Throwable $e) {
                    Log::warning('Form new response email failed: ' . $e->getMessage());
                }
            }
        }

        // 5. Send Confirmation Email to Respondent (if enabled and email found)
        if ($form->send_respondent_confirmation && $respondentEmail && filter_var($respondentEmail, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::to($respondentEmail)->send(new \App\Mail\FormRespondentConfirmationMail($form, $formResponse));
            } catch (\Throwable $e) {
                Log::warning('Form respondent confirmation email failed: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => $form->confirmation_message ?: 'Merci ! Votre réponse a été enregistrée avec succès.',
            'redirect_url' => $form->redirect_url ?: null,
            'response_uuid' => $formResponse->uuid,
        ]);
    }
}
