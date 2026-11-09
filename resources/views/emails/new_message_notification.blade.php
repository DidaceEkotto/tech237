<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau message privé sur Tech237</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f6f8; font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #333333;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f4f6f8; padding: 40px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.06); max-width: 600px; width: 100%;">
                    
                    <!-- Header -->
                    <tr>
                        <td align="center" style="background-color: #0B2559; padding: 28px 20px; border-bottom: 4px solid #00A859;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 26px; font-weight: 800; letter-spacing: -1px;">Tech237<span style="color: #00A859;">.</span></h1>
                            <p style="color: #ffffff; opacity: 0.85; margin: 5px 0 0 0; font-size: 13px;">Messagerie privée de la communauté Tech camerounaise</p>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 40px 35px;">
                            <div style="background-color: #e8f5e9; color: #2e7d32; display: inline-block; padding: 6px 14px; border-radius: 50px; font-weight: 700; font-size: 12px; margin-bottom: 20px; text-transform: uppercase;">
                                💬 Nouveau message privé
                            </div>

                            <h2 style="color: #111111; font-size: 20px; font-weight: 700; margin: 0 0 15px 0;">
                                Bonjour {{ $recipient->full_name ?? $recipient->name }},
                            </h2>

                            <p style="font-size: 15px; line-height: 1.6; color: #555555; margin-bottom: 20px;">
                                Vous avez reçu un nouveau message de <strong>{{ $sender->full_name ?? $sender->name }}</strong> sur votre espace membre Tech237.
                            </p>

                            <!-- Message Box Preview -->
                            <div style="background-color: #f9f9fb; border-left: 4px solid #00A859; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
                                <div style="font-size: 13px; font-weight: 700; color: #0B2559; margin-bottom: 8px;">
                                    Sujet : {{ $msgSubject }}
                                </div>
                                <div style="font-size: 14px; color: #333333; line-height: 1.6; white-space: pre-line;">
                                    {{ Str::limit($msgContent, 400) }}
                                </div>
                            </div>

                            <!-- CTA Button -->
                            <div align="center" style="margin-bottom: 30px;">
                                <a href="{{ $replyUrl }}" target="_blank" style="display: inline-block; background-color: #00A859; color: #ffffff; font-size: 15px; font-weight: 700; text-decoration: none; padding: 14px 34px; border-radius: 50px; box-shadow: 0 4px 15px rgba(0, 168, 89, 0.35);">
                                    👉 Répondre dans ma messagerie
                                </a>
                            </div>

                            <p style="font-size: 12px; color: #94a3b8; text-align: center; margin-top: 30px; border-top: 1px solid #f1f5f9; padding-top: 20px;">
                                Vous recevez cet email car vous êtes inscrit sur Tech237. Vos conversations privées restent chiffrées et confidentielles.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
