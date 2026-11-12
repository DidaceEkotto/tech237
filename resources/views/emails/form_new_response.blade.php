<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle réponse à votre formulaire - Tech237 Forms</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f1f5f9; margin: 0; padding: 24px; color: #1e293b;">
@php
    $siteSetting = \App\Models\SiteSetting::current();
    $logoUrl = $siteSetting->logo ? (str_starts_with($siteSetting->logo, 'http') ? $siteSetting->logo : asset($siteSetting->logo)) : asset('assets/images/logo.png');
    $siteName = $siteSetting->site_name ?? 'Tech237';
@endphp
    <table cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.06);">
        <!-- Header -->
        <tr>
            <td style="background: linear-gradient(135deg, #071738 0%, #0B2559 100%); padding: 26px 32px; text-align: center; border-bottom: 4px solid #00A859;">
                <a href="{{ url('/') }}" target="_blank" style="text-decoration: none; display: inline-block;">
                    <img src="{{ $logoUrl }}" alt="{{ $siteName }}" style="max-height: 44px; max-width: 180px; margin-bottom: 8px; display: block; margin-left: auto; margin-right: auto; border: 0;">
                </a>
                <h1 style="color: #ffffff; margin: 0; font-size: 20px; font-weight: 800; letter-spacing: -0.5px;">
                    Tech<span style="color: #00A859;">237</span> <span style="font-size: 15px; font-weight: 500; opacity: 0.9;">Forms</span>
                </h1>
                <p style="color: #cbd5e1; margin: 4px 0 0 0; font-size: 13px;">
                    Notification instantanée de réponse
                </p>
            </td>
        </tr>

        <!-- Banner -->
        <tr>
            <td style="background-color: #F0FDF4; border-bottom: 2px solid #86EFAC; padding: 16px 32px;">
                <table cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                        <td width="36" valign="middle">
                            <span style="font-size: 24px;">📝</span>
                        </td>
                        <td valign="middle">
                            <strong style="color: #166534; font-size: 14px;">Nouvelle soumission enregistrée !</strong>
                            <div style="color: #15803D; font-size: 12px;">Un participant vient de compléter votre formulaire <strong>« {{ $form->title }} »</strong>.</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Content -->
        <tr>
            <td style="padding: 32px;">
                <p style="font-size: 15px; line-height: 1.6; margin: 0 0 20px 0;">
                    Bonjour <strong>{{ $form->user->name }}</strong>,
                </p>
                <p style="font-size: 14px; line-height: 1.6; color: #475569; margin: 0 0 24px 0;">
                    Une nouvelle réponse a été soumise le <strong>{{ now()->translatedFormat('d F Y à H:i') }}</strong>.
                    @if($response->respondent_email)
                        <br>Répondant : <strong>{{ $response->respondent_email }}</strong>
                    @endif
                </p>

                <!-- Answers Table -->
                <div style="background-color: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0; padding: 20px; margin-bottom: 24px;">
                    <h3 style="margin: 0 0 16px 0; font-size: 15px; color: #0f172a; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">
                        Aperçu des réponses :
                    </h3>
                    @foreach($form->fields as $field)
                        @php
                            $val = $response->answers[$field->id] ?? $response->answers[$field->name] ?? null;
                        @endphp
                        @if($val !== null && $val !== '')
                            <div style="margin-bottom: 12px;">
                                <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">
                                    {{ $field->label }}
                                </div>
                                <div style="font-size: 14px; color: #1e293b; margin-top: 3px; font-weight: 500;">
                                    @if(is_array($val))
                                        {{ implode(', ', $val) }}
                                    @elseif(str_starts_with($val, 'http') || str_starts_with($val, '/storage/'))
                                        <a href="{{ str_starts_with($val, 'http') ? $val : url($val) }}" target="_blank" style="color: #00A859; text-decoration: underline;">Voir le lien / fichier</a>
                                    @else
                                        {{ Str::limit($val, 250) }}
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

                <!-- Call to Action -->
                <div style="text-align: center; margin: 30px 0 20px 0;">
                    <a href="{{ route('user.forms.responses', $form->id) }}" style="display: inline-block; background-color: #00A859; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 15px; padding: 14px 28px; border-radius: 50px; box-shadow: 0 4px 12px rgba(0, 168, 89, 0.35);">
                        📊 Voir toutes les réponses &amp; Télécharger le CSV
                    </a>
                </div>
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="background-color: #f8fafc; padding: 20px 32px; text-align: center; border-top: 1px solid #e2e8f0; font-size: 12px; color: #94a3b8;">
                Cet email automatique a été envoyé par <strong>{{ $siteName }} Forms</strong>.<br>
                Vous pouvez gérer vos préférences de notification depuis votre espace créateur.
            </td>
        </tr>
    </table>
</body>
</html>
