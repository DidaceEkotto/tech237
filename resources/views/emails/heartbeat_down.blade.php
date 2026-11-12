<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerte Incident - Tech237 Heartbeat</title>
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
            <td style="background: linear-gradient(135deg, #0B2559 0%, #1e3a8a 100%); padding: 26px 32px; text-align: center; border-bottom: 4px solid #ef4444;">
                <a href="{{ url('/') }}" target="_blank" style="text-decoration: none; display: inline-block;">
                    <img src="{{ $logoUrl }}" alt="{{ $siteName }}" style="max-height: 46px; max-width: 180px; margin-bottom: 8px; display: block; margin-left: auto; margin-right: auto; border: 0;">
                </a>
                <h1 style="color: #ffffff; margin: 0; font-size: 20px; font-weight: 800; letter-spacing: -0.5px;">
                    Tech<span style="color: #00A859;">237</span> <span style="font-size: 15px; font-weight: 500; opacity: 0.9;">Heartbeat</span>
                </h1>
                <p style="color: #cbd5e1; margin: 4px 0 0 0; font-size: 13px;">
                    Surveillance Uptime &amp; Santé de vos Projets et POCs
                </p>
            </td>
        </tr>

        <!-- Alert Banner -->
        <tr>
            <td style="background-color: #FEF2F2; border-bottom: 2px solid #FCA5A5; padding: 16px 32px;">
                <table cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                        <td width="36" valign="middle">
                            <span style="font-size: 24px;">🚨</span>
                        </td>
                        <td valign="middle">
                            <strong style="color: #991B1B; font-size: 14px;">
                                @if(!empty($isTest)) [TEST] Notification de simulation @else ALERTE : Site Détecté Hors-Ligne ! @endif
                            </strong>
                            <div style="color: #B91C1C; font-size: 12px;">Notre sonde Tech237 n'a pas pu joindre votre serveur.</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Content -->
        <tr>
            <td style="padding: 32px;">
                <p style="font-size: 15px; line-height: 1.6; margin: 0 0 18px 0;">
                    Bonjour,
                </p>

                <p style="font-size: 14px; line-height: 1.6; color: #475569; margin: 0 0 20px 0;">
                    La sonde de surveillance <strong>Tech237 Heartbeat</strong> a détecté une anomalie d'accessibilité sur votre site <strong>{{ $siteName }}</strong>.
                </p>

                <!-- Incident Box -->
                <div style="background-color: #fff1f2; border: 1px solid #fecdd3; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
                    <table cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td style="padding-bottom: 10px;">
                                <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #e11d48; margin-bottom: 4px;">Site concerné</div>
                                <div style="font-size: 15px; font-weight: 700; color: #0f172a;">{{ $siteName }}</div>
                                <div style="font-size: 13px; color: #64748b; word-break: break-all;">
                                    <a href="{{ $url }}" target="_blank" style="color: #2563eb; text-decoration: none;">{{ $url }}</a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style="border-top: 1px dashed #fca5a5; padding-top: 10px; padding-bottom: 10px;">
                                <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #e11d48; margin-bottom: 4px;">Raison de l'incident</div>
                                <div style="font-size: 13px; font-family: monospace; background-color: #ffe4e6; padding: 8px 12px; border-radius: 6px; color: #9f1239;">
                                    {{ $reason }}
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style="border-top: 1px dashed #fca5a5; padding-top: 10px;">
                                <div style="font-size: 12px; color: #64748b;">
                                    Détecté le : <strong>{{ $timeStr }}</strong>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Call to Action -->
                <div style="text-align: center; margin: 28px 0;">
                    <a href="{{ $dashboardUrl }}" style="display: inline-block; background: #0B2559; color: #ffffff; text-decoration: none; padding: 12px 28px; border-radius: 30px; font-weight: 700; font-size: 14px; box-shadow: 0 4px 10px rgba(11,37,89,0.2);">
                        Consulter mon tableau de bord Heartbeat &rarr;
                    </a>
                </div>

                <p style="font-size: 12px; line-height: 1.6; color: #94a3b8; margin: 0;">
                    Tech237 Heartbeat vérifie automatiquement vos projets à intervalle régulier. Une notification vous sera envoyée dès que votre serveur sera à nouveau accessible.
                </p>
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px; text-align: center; font-size: 12px; color: #64748b;">
                Tech237 &bull; Plateforme de valorisation des créateurs de technologies au Cameroun<br>
                <a href="{{ url('/') }}" style="color: #00A859; text-decoration: none; font-weight: 600;">tech237.net</a>
            </td>
        </tr>
    </table>
</body>
</html>
