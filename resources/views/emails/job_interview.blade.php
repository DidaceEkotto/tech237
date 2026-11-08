<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convocation à un entretien de recrutement</title>
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
                            <p style="color: #ffffff; opacity: 0.8; margin: 5px 0 0 0; font-size: 13px;">Plateforme de l'écosystème Tech au Cameroun</p>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 40px 35px;">
                            <div style="background-color: #e8f5e9; color: #2e7d32; display: inline-block; padding: 6px 14px; border-radius: 50px; font-weight: 700; font-size: 12px; margin-bottom: 20px; text-transform: uppercase;">
                                🎯 Convocation à un entretien
                            </div>

                            <h2 style="color: #111111; font-size: 22px; font-weight: 700; margin: 0 0 15px 0;">
                                Félicitations {{ $application->name }},
                            </h2>

                            <p style="font-size: 15px; line-height: 1.6; color: #555555; margin-bottom: 25px;">
                                Suite à l'examen attentif de votre candidature pour le poste de <strong>{{ $job->title }}</strong>, nous avons le plaisir de vous convoquer à un entretien d'embauche.
                            </p>

                            <!-- Interview Details Card -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f9f9fb; border: 1px solid #eeeeee; border-radius: 12px; margin-bottom: 30px;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="8" border="0">
                                            <tr>
                                                <td width="35%" style="color: #777777; font-size: 13px; font-weight: 600;">Poste :</td>
                                                <td style="color: #111111; font-size: 14px; font-weight: 700;">{{ $job->title }}</td>
                                            </tr>
                                            <tr>
                                                <td style="color: #777777; font-size: 13px; font-weight: 600;">Date de l'entretien :</td>
                                                <td style="color: #111111; font-size: 14px; font-weight: 700;">{{ \Carbon\Carbon::parse($interview->interview_date)->translatedFormat('l d F Y') }}</td>
                                            </tr>
                                            <tr>
                                                <td style="color: #777777; font-size: 13px; font-weight: 600;">Heure :</td>
                                                <td style="color: #00A859; font-size: 14px; font-weight: 700;">{{ $interview->interview_time }}</td>
                                            </tr>
                                            <tr>
                                                <td style="color: #777777; font-size: 13px; font-weight: 600;">Lieu / Modalités :</td>
                                                <td style="color: #111111; font-size: 14px; font-weight: 600;">{{ $interview->location }}</td>
                                            </tr>
                                            @if(!empty($interview->notes))
                                            <tr>
                                                <td style="color: #777777; font-size: 13px; font-weight: 600; vertical-align: top;">Instructions du recruteur :</td>
                                                <td style="color: #444444; font-size: 13px; line-height: 1.5;">{{ $interview->notes }}</td>
                                            </tr>
                                            @endif
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            @if(!empty($customMessage))
                            <div style="background-color: #ffffff; border-left: 4px solid #00A859; padding: 15px 20px; margin-bottom: 25px; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                                <h4 style="margin: 0 0 8px 0; font-size: 14px; color: #111111;">Message personnalisé du recruteur :</h4>
                                <p style="margin: 0; font-size: 13px; line-height: 1.6; color: #555555; white-space: pre-line;">{{ $customMessage }}</p>
                            </div>
                            @endif

                            <p style="font-size: 14px; line-height: 1.6; color: #555555; margin-bottom: 25px;">
                                En cas d'empêchement ou pour toute question préalable, vous pouvez répondre directement à cet email.
                            </p>

                            <p style="font-size: 14px; line-height: 1.6; color: #555555; margin-bottom: 0;">
                                Nous vous souhaitons une excellente préparation.<br>
                                Cordialement,<br>
                                <strong>L'équipe de recrutement & Tech237</strong>
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
