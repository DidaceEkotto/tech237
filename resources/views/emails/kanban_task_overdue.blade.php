<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerte Échéance Dépassée - Kanban Tech237</title>
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
            <td style="background: linear-gradient(135deg, #0B2559 0%, #1e3a8a 100%); padding: 26px 32px; text-align: center; border-bottom: 4px solid #f59e0b;">
                <a href="{{ url('/') }}" target="_blank" style="text-decoration: none; display: inline-block;">
                    <img src="{{ $logoUrl }}" alt="{{ $siteName }}" style="max-height: 46px; max-width: 180px; margin-bottom: 8px; display: block; margin-left: auto; margin-right: auto; border: 0;">
                </a>
                <h1 style="color: #ffffff; margin: 0; font-size: 20px; font-weight: 800; letter-spacing: -0.5px;">
                    Tech<span style="color: #00A859;">237</span> <span style="font-size: 15px; font-weight: 500; opacity: 0.9;">Kanban</span>
                </h1>
                <p style="color: #cbd5e1; margin: 4px 0 0 0; font-size: 13px;">
                    Gestionnaire de Projets &amp; Tableaux Kanban
                </p>
            </td>
        </tr>

        <!-- Alert Banner -->
        <tr>
            <td style="background-color: #FEF2F2; border-bottom: 2px solid #FCA5A5; padding: 16px 32px;">
                <table cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                        <td width="36" valign="middle">
                            <span style="font-size: 24px;">⚠️</span>
                        </td>
                        <td valign="middle">
                            <strong style="color: #991B1B; font-size: 14px;">Alerte automatique d'échéance non atteinte</strong>
                            <div style="color: #B91C1C; font-size: 12px;">Une tâche de votre projet a dépassé sa date limite sans être complétée.</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Content -->
        <tr>
            <td style="padding: 32px;">
                <p style="font-size: 15px; line-height: 1.6; margin: 0 0 20px 0;">
                    Bonjour <strong>{{ $user->name }}</strong>,
                </p>

                <p style="font-size: 14px; line-height: 1.6; color: #475569; margin: 0 0 24px 0;">
                    En tant que contributeur ou responsable sur le projet 
                    <strong>{{ $project ? $project->name : ($board ? $board->title : 'Tech237') }}</strong>, 
                    nous vous informons qu'un objectif n'a pas été atteint dans les délais prévus :
                </p>

                <!-- Task Card Box -->
                <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
                    <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #ef4444; margin-bottom: 6px;">
                        Tâche en retard &bull; Échéance : {{ \Carbon\Carbon::parse($card->due_date)->format('d/m/Y') }}
                    </div>
                    <h3 style="font-size: 17px; font-weight: 700; color: #0f172a; margin: 0 0 10px 0;">
                        {{ $card->title }}
                    </h3>
                    @if($card->description)
                        <p style="font-size: 13px; color: #64748b; line-height: 1.5; margin: 0 0 12px 0;">
                            {{ \Illuminate\Support\Str::limit($card->description, 160) }}
                        </p>
                    @endif
                    <table cellpadding="0" cellspacing="0" width="100%" style="font-size: 12px; color: #64748b; border-top: 1px dashed #cbd5e1; padding-top: 10px;">
                        <tr>
                            <td><strong>Tableau :</strong> {{ $board->title }}</td>
                            <td align="right">
                                <strong>Assigné à :</strong> 
                                {{ $card->assignee ? $card->assignee->name : 'Non assigné' }}
                            </td>
                        </tr>
                    </table>
                </div>

                <p style="font-size: 13px; color: #475569; line-height: 1.5; margin: 0 0 28px 0;">
                    Vous pouvez vous coordonner avec vos collaborateurs pour réajuster la priorité, mettre à jour la date d'échéance ou marquer la tâche comme terminée.
                </p>

                <!-- CTA Button -->
                <div style="text-align: center; margin-bottom: 20px;">
                    <a href="{{ $kanbanUrl }}" style="display: inline-block; background-color: #00A859; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 14px; padding: 14px 32px; border-radius: 50px; box-shadow: 0 4px 12px rgba(0, 168, 89, 0.3);">
                        Accéder au Tableau Kanban &rarr;
                    </a>
                </div>
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 32px; text-align: center; font-size: 11px; color: #94a3b8;">
                Cet email a été envoyé automatiquement par le module Kanban de Tech237.<br>
                Tech237 &mdash; Là où les idées deviennent des projets au Cameroun.
            </td>
        </tr>
    </table>
</body>
</html>
