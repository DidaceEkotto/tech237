<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incident Résolu - Tech237 Heartbeat</title>
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
            <td style="background: linear-gradient(135deg, #0B2559 0%, #1e3a8a 100%); padding: 26px 32px; text-align: center; border-bottom: 4px solid #10b981;">
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

        <!-- Recovery Banner -->
        <tr>
            <td style="background-color: #ECFDF5; border-bottom: 2px solid #6EE7B7; padding: 16px 32px;">
                <table cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                        <td width="36" valign="middle">
                            <span style="font-size: 24px;">✅</span>
                        </td>
                        <td valign="middle">
                            <strong style="color: #065F46; font-size: 14px;">INCIDENT RÉSOLU : Votre site est de nouveau en ligne !</strong>
                            <div style="color: #047857; font-size: 12px;">Notre sonde a réussi à joindre votre serveur avec succès.</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Content -->
        <tr>
            <td style="padding: 32px;">
                <p style="font-size: 15px; line-height: 1.6; margin: 0 0 18px 0;">
                    Bonne nouvelle !
                </p>

                <p style="font-size: 14px; line-height: 1.6; color: #475569; margin: 0 0 20px 0;">
                    Votre projet <strong>{{ $siteName }}</strong> a répondu positivement aux sondes de <strong>Tech237 Heartbeat</strong>. Les services sont rétablis.
                </p>

                <!-- Status Details Box -->
                <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
                    <table cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td style="padding-bottom: 10px;">
                                <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #15803d; margin-bottom: 4px;">Site rétabli</div>
                                <div style="font-size: 15px; font-weight: 700; color: #0f172a;">{{ $siteName }}</div>
                                <div style="font-size: 13px; color: #64748b; word-break: break-all;">
                                    <a href="{{ $url }}" target="_blank" style="color: #2563eb; text-decoration: none;">{{ $url }}</a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style="border-top: 1px dashed #86efac; padding-top: 10px; padding-bottom: 10px;">
                                <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #15803d; margin-bottom: 4px;">Performance actuelle</div>
                                <div style="font-size: 14px; color: #166534; font-weight: 600;">
                                    Temps de réponse : <span style="background-color: #dcfce7; padding: 3px 8px; border-radius: 4px;">{{ $latencyMs }} ms</span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style="border-top: 1px dashed #86efac; padding-top: 10px;">
                                <div style="font-size: 12px; color: #64748b;">
                                    Rétablissement confirmé le : <strong>{{ $timeStr }}</strong>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Call to Action -->
                <div style="text-align: center; margin: 28px 0;">
                    <a href="{{ $dashboardUrl }}" style="display: inline-block; background: #00A859; color: #ffffff; text-decoration: none; padding: 12px 28px; border-radius: 30px; font-weight: 700; font-size: 14px; box-shadow: 0 4px 10px rgba(0,168,89,0.25);">
                        Voir mon tableau de bord Heartbeat &rarr;
                    </a>
                </div>

                <p style="font-size: 12px; line-height: 1.6; color: #94a3b8; margin: 0;">
                    Tech237 Heartbeat continue de surveiller la disponibilité de vos services en continu.
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
