<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation de votre mot de passe</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f6f8; font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #333333;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f4f6f8; padding: 40px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.06); max-width: 600px; width: 100%;">
                    
                    <!-- Header -->
                    <tr>
                        <td align="center" style="background-color: #0B2559; padding: 30px 20px; border-bottom: 4px solid #00A859;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 26px; font-weight: 800; letter-spacing: -1px;">Tech237<span style="color: #00A859;">.</span></h1>
                            <p style="color: #ffffff; opacity: 0.8; margin: 5px 0 0 0; font-size: 13px;">Là où les idées deviennent des projets</p>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 40px 35px;">
                            <div style="background-color: #fff3cd; color: #856404; display: inline-block; padding: 6px 14px; border-radius: 50px; font-weight: 700; font-size: 12px; margin-bottom: 20px; text-transform: uppercase;">
                                🔒 Sécurité du compte
                            </div>

                            <h2 style="color: #111111; font-size: 22px; font-weight: 700; margin: 0 0 15px 0;">
                                Réinitialisation de votre mot de passe
                            </h2>

                            <p style="font-size: 15px; line-height: 1.6; color: #555555; margin-bottom: 25px;">
                                Bonjour,<br><br>
                                Vous recevez cet email car une demande de réinitialisation de mot de passe a été effectuée pour votre compte sur <strong>Tech237</strong>.
                            </p>

                            <div style="text-align: center; margin: 35px 0;">
                                <a href="{{ $resetUrl }}" target="_blank" style="background-color: #00A859; color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 50px; font-size: 15px; font-weight: 700; display: inline-block; box-shadow: 0 4px 15px rgba(0, 168, 89, 0.35);">
                                    Réinitialiser mon mot de passe
                                </a>
                            </div>

                            <p style="font-size: 13px; line-height: 1.6; color: #777777; margin-bottom: 20px;">
                                ⚠️ <strong>Important :</strong> Ce lien sécurisé est valable pendant <strong>10 minutes</strong>. Une fois ce délai dépassé, vous devrez formuler une nouvelle demande.
                            </p>

                            <p style="font-size: 13px; line-height: 1.6; color: #777777; margin-bottom: 30px;">
                                Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer cet email en toute sécurité. Votre mot de passe actuel restera inchangé.
                            </p>

                            <hr style="border: none; border-top: 1px solid #eeeeee; margin: 30px 0;">

                            <p style="font-size: 12px; color: #999999; line-height: 1.5; word-break: break-all;">
                                Si le bouton ne fonctionne pas, copiez et collez l'adresse suivante dans votre navigateur :<br>
                                <a href="{{ $resetUrl }}" style="color: #00A859;">{{ $resetUrl }}</a>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="background-color: #f9f9fb; padding: 25px 20px; font-size: 12px; color: #888888; border-top: 1px solid #eeeeee;">
                            © {{ date('Y') }} Tech237 Cameroun. Tous droits réservés.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
