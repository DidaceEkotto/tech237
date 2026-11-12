<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation d'inscription</title>
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
                    
                    <!-- Header with Site Logo -->
                    <tr>
                        <td align="center" style="background-color: #0B2559; padding: 26px 20px; border-bottom: 4px solid #00A859;">
                            <a href="{{ url('/') }}" target="_blank" style="text-decoration: none; display: inline-block;">
                                <img src="{{ $logoUrl }}" alt="{{ $siteName }}" style="max-height: 48px; max-width: 190px; margin-bottom: 8px; display: block; border: 0;">
                            </a>
                            <p style="color: #ffffff; opacity: 0.85; margin: 0; font-size: 13px;">{{ $siteName }} &bull; Événements &amp; Conférences Tech</p>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 40px 35px;">
                            <div style="background-color: #e8f5e9; color: #2e7d32; display: inline-block; padding: 6px 14px; border-radius: 50px; font-weight: 700; font-size: 13px; margin-bottom: 20px; text-transform: uppercase;">
                                ✓ Inscription Confirmée
                            </div>

                            <h2 style="color: #111111; font-size: 24px; font-weight: 700; margin: 0 0 15px 0;">
                                Bonjour {{ $registration->first_name }} {{ $registration->name }},
                            </h2>

                            <p style="font-size: 15px; line-height: 1.6; color: #555555; margin-bottom: 25px;">
                                Nous avons le plaisir de vous confirmer votre participation officielle à l'événement <strong>{{ $conference->title_fr }}</strong>. Voici votre récapitulatif d'inscription ainsi que vos accès.
                            </p>

                            <!-- Event Details Card -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f9f9fb; border: 1px solid #eeeeee; border-radius: 12px; margin-bottom: 30px;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="6" border="0">
                                            <tr>
                                                <td width="35%" style="color: #777777; font-size: 13px; font-weight: 600;">Événement :</td>
                                                <td style="color: #111111; font-size: 14px; font-weight: 700;">{{ $conference->title_fr }}</td>
                                            </tr>
                                            <tr>
                                                <td style="color: #777777; font-size: 13px; font-weight: 600;">Date & Heure :</td>
                                                <td style="color: #111111; font-size: 14px; font-weight: 600;">
                                                    {{ $conference->event_date ? $conference->event_date->format('d/m/Y') : 'À venir' }}
                                                    @if($conference->end_date && $conference->end_date->ne($conference->event_date))
                                                        au {{ $conference->end_date->format('d/m/Y') }}
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="color: #777777; font-size: 13px; font-weight: 600;">Lieu & Ville :</td>
                                                <td style="color: #111111; font-size: 14px; font-weight: 600;">{{ $conference->location ?: $conference->city }} ({{ $conference->city }})</td>
                                            </tr>
                                            <tr>
                                                <td style="color: #777777; font-size: 13px; font-weight: 600;">Billet N° :</td>
                                                <td style="color: #FF004B; font-size: 15px; font-weight: 800; font-family: monospace;">#CONF-{{ $conference->id }}-{{ str_pad($registration->id, 5, '0', STR_PAD_LEFT) }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size: 14px; line-height: 1.6; color: #666666; margin-bottom: 30px;">
                                Veuillez vous munir de ce billet électronique ou de votre pièce d'identité lors de votre arrivée sur le lieu de la conférence.
                            </p>

                            <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td align="center" style="border-radius: 50px; background-color: #FF004B;">
                                        <a href="{{ url('/conference/' . $conference->slug) }}" target="_blank" style="font-size: 14px; font-weight: 700; color: #ffffff; text-decoration: none; padding: 14px 30px; display: inline-block; border-radius: 50px;">
                                            Consulter la page de l'événement →
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #fafafa; border-top: 1px solid #eeeeee; padding: 25px 35px; text-align: center;">
                            <p style="margin: 0 0 8px 0; font-size: 12px; color: #999999;">
                                Cet email vous a été envoyé par <strong>{{ $settings->site_name ?? 'Tech237' }}</strong> dans le cadre de votre inscription à un événement tech.
                            </p>
                            <p style="margin: 0; font-size: 12px; color: #aaaaaa;">
                                © {{ date('Y') }} {{ $settings->site_name ?? 'Tech237' }}. Tous droits réservés.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
