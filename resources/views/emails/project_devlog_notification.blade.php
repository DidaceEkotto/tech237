<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau Devlog sur {{ $project->name }}</title>
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
                                🚀 Nouveau Devlog &bull; Projet Favori
                            </div>

                            <h2 style="color: #111111; font-size: 22px; font-weight: 700; margin: 0 0 15px 0;">
                                Bonjour {{ $subscriber->name }},
                            </h2>

                            <p style="font-size: 15px; line-height: 1.6; color: #555555; margin-bottom: 25px;">
                                Une nouvelle étape de développement (Devlog) vient d'être publiée sur le projet <strong>« {{ $project->name }} »</strong> que vous avez sauvegardé dans vos favoris !
                            </p>

                            <!-- Project Card Header -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; margin-bottom: 25px; overflow: hidden;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td width="64" valign="top">
                                                    @if(!empty($project->logo))
                                                        <img src="{{ url($project->logo) }}" alt="{{ $project->name }}" width="56" height="56" style="border-radius: 12px; object-fit: contain; border: 1px solid #e2e8f0; background: #ffffff; padding: 2px;">
                                                    @else
                                                        <div style="width: 56px; height: 56px; border-radius: 12px; background-color: #00A859; color: #ffffff; font-size: 22px; font-weight: 800; text-align: center; line-height: 56px;">
                                                            {{ strtoupper(substr($project->name, 0, 1)) }}
                                                        </div>
                                                    @endif
                                                </td>
                                                <td style="padding-left: 16px;" valign="top">
                                                    <span style="display: inline-block; background-color: #dbeafe; color: #1e40af; font-size: 10px; font-weight: 700; text-transform: uppercase; padding: 2px 8px; border-radius: 20px; margin-bottom: 4px;">
                                                        PROJET SUIVI
                                                    </span>
                                                    <h3 style="margin: 0 0 4px 0; color: #0B2559; font-size: 17px; font-weight: 700;">
                                                        {{ $project->name }}
                                                    </h3>
                                                    <p style="margin: 0; font-size: 13px; color: #64748b;">
                                                        {{ \Illuminate\Support\Str::limit(strip_tags($project->description), 110) }}
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Devlog Step Box -->
                            <div style="background-color: #ffffff; border: 2px solid #00A859; border-radius: 12px; padding: 20px; margin-bottom: 30px; box-shadow: 0 4px 12px rgba(0,168,89,0.08);">
                                <div style="font-size: 12px; font-weight: 700; color: #00A859; text-transform: uppercase; margin-bottom: 6px;">
                                    📝 Étape du Journal de Bord
                                </div>
                                <h4 style="color: #0B2559; margin: 0 0 10px 0; font-size: 18px; font-weight: 700;">
                                    {{ $timeline->title }}
                                </h4>
                                @if(!empty($timeline->description))
                                    <p style="color: #475569; font-size: 14px; line-height: 1.6; margin: 0 0 12px 0;">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($timeline->description), 260) }}
                                    </p>
                                @endif

                                @if(!empty($timeline->solution_applied))
                                    <div style="background-color: #f0fdf4; border-left: 3px solid #00A859; padding: 10px 14px; border-radius: 6px; font-size: 13px; color: #166534; margin-top: 10px;">
                                        <strong>Solution / Amélioration :</strong> {{ \Illuminate\Support\Str::limit(strip_tags($timeline->solution_applied), 180) }}
                                    </div>
                                @endif
                            </div>

                            <!-- CTA Button -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 30px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $projectUrl }}" target="_blank" style="background-color: #00A859; color: #ffffff; text-decoration: none; padding: 14px 34px; border-radius: 50px; font-weight: 700; font-size: 15px; display: inline-block; box-shadow: 0 4px 15px rgba(0,168,89,0.35);">
                                            Consulter le Devlog sur Tech237 &rarr;
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size: 12px; color: #94a3b8; line-height: 1.5; margin-bottom: 0; text-align: center;">
                                Vous recevez cet email car vous avez ajouté le projet <strong>{{ $project->name }}</strong> à vos favoris.<br>
                                <a href="{{ $projectUrl }}" style="color: #00A859; text-decoration: underline;">Voir la page du projet</a>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="background-color: #f8fafc; padding: 25px 20px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #94a3b8;">
                            &copy; {{ date('Y') }} Tech237. Tous droits réservés.<br>
                            Média &amp; Écosystème de l'Innovation Technologique au Cameroun.
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
