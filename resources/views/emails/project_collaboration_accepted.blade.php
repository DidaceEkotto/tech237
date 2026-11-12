<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Un nouveau collaborateur a rejoint votre projet {{ $project->name }}</title>
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
                                <img src="{{ $logoUrl }}" alt="{{ $siteName }}" style="max-height: 48px; max-width: 190px; margin-bottom: 8px; display: block; border: 0;">
                            </a>
                            <p style="color: #ffffff; opacity: 0.85; margin: 0; font-size: 13px;">{{ $siteName }} &bull; Plateforme de l'écosystème Tech au Cameroun</p>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 40px 35px;">
                            <div style="background-color: #e8f5e9; color: #2e7d32; display: inline-block; padding: 6px 14px; border-radius: 50px; font-weight: 700; font-size: 12px; margin-bottom: 20px; text-transform: uppercase;">
                                🎉 Bonne nouvelle !
                            </div>

                            <h2 style="color: #111111; font-size: 22px; font-weight: 700; margin: 0 0 15px 0;">
                                Bonjour {{ $project->user->full_name || $project->user->name }},
                            </h2>

                            <p style="font-size: 15px; line-height: 1.6; color: #555555; margin-bottom: 25px;">
                                <strong>{{ $collaborator->full_name || $collaborator->name }}</strong> ({{ $collaborator->email }}) a accepté votre invitation et collaborera désormais avec vous sur le projet <strong>{{ $project->name }}</strong>.
                            </p>

                            <!-- Collaboration Details Card -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f9f9fb; border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 30px;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="8" border="0">
                                            <tr>
                                                <td width="35%" style="color: #777777; font-size: 13px; font-weight: 600;">Projet :</td>
                                                <td style="color: #111111; font-size: 14px; font-weight: 700;">{{ $project->name }}</td>
                                            </tr>
                                            <tr>
                                                <td style="color: #777777; font-size: 13px; font-weight: 600;">Collaborateur :</td>
                                                <td style="color: #111111; font-size: 14px; font-weight: 700;">{{ $collaborator->full_name || $collaborator->name }}</td>
                                            </tr>
                                            <tr>
                                                <td style="color: #777777; font-size: 13px; font-weight: 600;">Droits accordés :</td>
                                                <td style="color: #00A859; font-size: 14px; font-weight: 600;">Ajout d'images et soumission d'étapes Devlogs (soumises à votre validation)</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size: 14px; line-height: 1.6; color: #555555; margin-bottom: 30px;">
                                Les étapes Devlogs et images soumises par ce collaborateur apparaîtront dans votre tableau de bord où vous pourrez les valider avant leur publication sur la fiche publique du projet. Vous pouvez également annuler cette collaboration à tout moment.
                            </p>

                            <!-- CTA Button -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 20px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ url('/espace-utilisateur?section=projects') }}" target="_blank" style="background-color: #0B2559; color: #ffffff; text-decoration: none; padding: 14px 34px; border-radius: 50px; font-weight: 700; font-size: 15px; display: inline-block;">
                                            Accéder à mon espace projet &rarr;
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="background-color: #f8fafc; padding: 20px 35px; border-top: 1px solid #eeeeee;">
                            <p style="color: #999999; font-size: 11px; margin: 0; line-height: 1.5;">
                                Tech237 - La vitrine technologique du Cameroun
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
