<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitation à collaborer sur le projet {{ $project->name }}</title>
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
                                🤝 Invitation à collaborer
                            </div>

                            <h2 style="color: #111111; font-size: 22px; font-weight: 700; margin: 0 0 15px 0;">
                                Bonjour,
                            </h2>

                            <p style="font-size: 15px; line-height: 1.6; color: #555555; margin-bottom: 25px;">
                                <strong>{{ $inviter->full_name || $inviter->name }}</strong> vous invite à rejoindre l'équipe de développement et à collaborer sur son projet tech sur Tech237 :
                            </p>

                            <!-- Project Card Preview -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f9f9fb; border: 1px solid #e2e8f0; border-radius: 14px; margin-bottom: 25px; overflow: hidden;">
                                <tr>
                                    <td style="padding: 24px;">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td width="70" valign="top">
                                                    @if(!empty($project->logo))
                                                        <img src="{{ url($project->logo) }}" alt="{{ $project->name }}" width="64" height="64" style="border-radius: 12px; object-fit: contain; border: 1px solid #e2e8f0; background: #ffffff; padding: 2px;">
                                                    @else
                                                        <div style="width: 64px; height: 64px; border-radius: 12px; background-color: #00A859; color: #ffffff; font-size: 24px; font-weight: 800; text-align: center; line-height: 64px;">
                                                            {{ strtoupper(substr($project->name, 0, 1)) }}
                                                        </div>
                                                    @endif
                                                </td>
                                                <td style="padding-left: 18px;" valign="top">
                                                    <span style="display: inline-block; background-color: #fee2e2; color: #dc2626; font-size: 10px; font-weight: 700; text-transform: uppercase; padding: 3px 8px; border-radius: 20px; margin-bottom: 6px;">
                                                        {{ strtoupper($project->project_type) }}
                                                    </span>
                                                    <h3 style="margin: 0 0 6px 0; color: #0B2559; font-size: 18px; font-weight: 700;">
                                                        {{ $project->name }}
                                                    </h3>
                                                    @if($project->startup)
                                                        <div style="font-size: 12px; color: #64748b; margin-bottom: 6px;">
                                                            🏢 Porté par <strong>{{ $project->startup->name }}</strong>
                                                        </div>
                                                    @endif
                                                    <p style="margin: 0; font-size: 13px; line-height: 1.5; color: #64748b;">
                                                        {{ \Illuminate\Support\Str::limit(strip_tags($project->description), 140) }}
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            @if(!empty($invitation->message))
                            <div style="background-color: #f1f5f9; border-left: 4px solid #0B2559; padding: 14px 18px; border-radius: 8px; margin-bottom: 25px; font-size: 14px; font-style: italic; color: #334155;">
                                « {{ $invitation->message }} »
                            </div>
                            @endif

                            <!-- Expiration alert -->
                            <div style="background-color: #fffbeb; border: 1px solid #fef3c7; border-radius: 10px; padding: 12px 16px; margin-bottom: 28px; font-size: 13px; color: #92400e; display: flex; align-items: center;">
                                ⏱️ <strong>Important :</strong> Ce lien d'invitation est valable pendant <strong>48 heures</strong>. Passé ce délai, il deviendra automatiquement inutilisable.
                            </div>

                            <!-- CTA Button -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 30px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $inviteUrl }}" target="_blank" style="background-color: #00A859; color: #ffffff; text-decoration: none; padding: 14px 34px; border-radius: 50px; font-weight: 700; font-size: 15px; display: inline-block; box-shadow: 0 4px 15px rgba(0,168,89,0.35);">
                                            Consulter &amp; Répondre à l'invitation &rarr;
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size: 12px; color: #94a3b8; line-height: 1.5; margin-bottom: 0;">
                                Si le bouton ci-dessus ne fonctionne pas, copiez-collez ce lien sécurisé dans votre navigateur :<br>
                                <a href="{{ $inviteUrl }}" style="color: #00A859; word-break: break-all;">{{ $inviteUrl }}</a>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="background-color: #f8fafc; padding: 20px 35px; border-top: 1px solid #eeeeee;">
                            <p style="color: #999999; font-size: 11px; margin: 0; line-height: 1.5;">
                                Cet email a été envoyé via <strong>Tech237</strong> à l'attention de {{ $invitation->email }}.<br>
                                Si vous ne souhaitez jamais recevoir d'invitations de ce créateur, vous pouvez choisir de bloquer toute collaboration future depuis la page d'invitation.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
