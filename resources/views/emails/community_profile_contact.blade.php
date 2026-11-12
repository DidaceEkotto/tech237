<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau message reçu depuis votre profil public Tech237</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f6f8; font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #333333;">
@php
    $siteSetting = \App\Models\SiteSetting::current();
    $logoUrl = $siteSetting->logo ? (str_starts_with($siteSetting->logo, 'http') ? $siteSetting->logo : asset($siteSetting->logo)) : asset('assets/images/logo.png');
    $siteName = $siteSetting->site_name ?? 'Tech237';
@endphp
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f4f6f8; padding: 40px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.06); max-width: 600px; width: 100%;">
                    
                    <!-- Header -->
                    <tr>
                        <td align="center" style="background-color: #0B2559; padding: 26px 20px; border-bottom: 4px solid #00A859;">
                            <a href="{{ url('/') }}" target="_blank" style="text-decoration: none; display: inline-block;">
                                <img src="{{ $logoUrl }}" alt="{{ $siteName }}" style="max-height: 48px; max-width: 190px; margin-bottom: 8px; display: block; border: 0;" />
                            </a>
                            <p style="color: #ffffff; opacity: 0.85; margin: 0; font-size: 13px;">Plateforme &amp; Communauté Tech Camerounaise</p>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 40px 35px;">
                            <div style="background-color: #e8f5e9; color: #2e7d32; display: inline-block; padding: 6px 14px; border-radius: 50px; font-weight: 700; font-size: 12px; margin-bottom: 20px; text-transform: uppercase;">
                                ✉️ Nouveau message depuis votre profil public
                            </div>

                            <h2 style="color: #111111; font-size: 20px; font-weight: 700; margin: 0 0 15px 0;">
                                Bonjour {{ $recipient->full_name ?? $recipient->name }},
                            </h2>

                            <p style="font-size: 15px; line-height: 1.6; color: #555555; margin-bottom: 20px;">
                                Un visiteur ou membre de la communauté Tech237 vient de vous envoyer un message direct depuis votre profil public.
                            </p>

                            <!-- Expéditeur Details -->
                            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px 20px; margin-bottom: 20px;">
                                <div style="font-size: 14px; margin-bottom: 6px;">
                                    <strong>Expéditeur :</strong> {{ $sender_name }}
                                </div>
                                <div style="font-size: 14px; margin-bottom: 6px;">
                                    <strong>Email de contact :</strong> <a href="mailto:{{ $sender_email }}" style="color: #00A859; text-decoration: none; font-weight: 600;">{{ $sender_email }}</a>
                                </div>
                                <div style="font-size: 14px;">
                                    <strong>Sujet :</strong> {{ $subject }}
                                </div>
                            </div>

                            <!-- Message Content -->
                            <div style="background-color: #ffffff; border-left: 4px solid #00A859; padding: 20px; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); margin-bottom: 30px; border: 1px solid #edf2f7;">
                                <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 10px; letter-spacing: 0.5px;">Message :</div>
                                <div style="font-size: 14px; color: #2d3748; line-height: 1.7; white-space: pre-line;">{{ $message_content }}</div>
                            </div>

                            <!-- CTA Button (Direct Reply via Email Client) -->
                            <div align="center" style="margin-bottom: 30px;">
                                <a href="mailto:{{ $sender_email }}?subject=Re: {{ rawurlencode($subject) }}" target="_blank" style="display: inline-block; background-color: #00A859; color: #ffffff; font-size: 15px; font-weight: 700; text-decoration: none; padding: 14px 34px; border-radius: 50px; box-shadow: 0 4px 15px rgba(0, 168, 89, 0.35);">
                                    👉 Répondre directement à {{ $sender_name }}
                                </a>
                            </div>

                            <p style="font-size: 12px; color: #94a3b8; text-align: center; margin-top: 30px; border-top: 1px solid #f1f5f9; padding-top: 20px;">
                                Vous recevez cet email via le formulaire de contact de votre profil public sur {{ $siteName }}. Vous pouvez répondre directement en cliquant sur le bouton ci-dessus ou en répondant à ce courriel.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>