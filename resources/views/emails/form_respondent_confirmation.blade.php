<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de votre soumission - Tech237 Forms</title>
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
                    Accusé de réception de votre soumission
                </p>
            </td>
        </tr>

        <!-- Banner -->
        <tr>
            <td style="background-color: #F0FDF4; border-bottom: 2px solid #86EFAC; padding: 16px 32px;">
                <table cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                        <td width="36" valign="middle">
                            <span style="font-size: 24px;">✅</span>
                        </td>
                        <td valign="middle">
                            <strong style="color: #166534; font-size: 14px;">Votre réponse a été bien enregistrée !</strong>
                            <div style="color: #15803D; font-size: 12px;">Formulaire : <strong>« {{ $form->title }} »</strong></div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Content -->
        <tr>
            <td style="padding: 32px;">
                <p style="font-size: 15px; line-height: 1.6; margin: 0 0 16px 0;">
                    Bonjour,
                </p>
                <p style="font-size: 14px; line-height: 1.6; color: #475569; margin: 0 0 20px 0;">
                    Merci d'avoir pris le temps de répondre au formulaire <strong>« {{ $form->title }} »</strong> organisé par <strong>{{ $form->user->name }}</strong> sur {{ $siteName }}.
                </p>

                @if($form->confirmation_message)
                    <div style="background-color: #ECFDF5; border-left: 4px solid #10B981; padding: 16px; border-radius: 8px; margin-bottom: 24px; font-size: 14px; color: #065F46;">
                        <strong>Note de l'auteur :</strong><br>
                        {!! nl2br(e($form->confirmation_message)) !!}
                    </div>
                @endif

                <p style="font-size: 13px; color: #64748b;">
                    Date de transmission : {{ now()->translatedFormat('d F Y à H:i') }}<br>
                    Identifiant unique de référence : <code style="background-color: #f1f5f9; padding: 2px 6px; border-radius: 4px;">{{ $response->uuid }}</code>
                </p>
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="background-color: #f8fafc; padding: 20px 32px; text-align: center; border-top: 1px solid #e2e8f0; font-size: 12px; color: #94a3b8;">
                Propulsé par <strong>{{ $siteName }} Forms</strong> — La plateforme tech du Cameroun.
            </td>
        </tr>
    </table>
</body>
</html>
