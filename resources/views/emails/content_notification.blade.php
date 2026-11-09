<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvel Article : {{ $articleTitle }}</title>
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
                            <p style="color: #ffffff; opacity: 0.8; margin: 5px 0 0 0; font-size: 13px;">Plateforme &amp; Médias Tech au Cameroun</p>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 40px 35px;">
                            <div style="background-color: #eff6ff; color: #1e40af; display: inline-block; padding: 6px 14px; border-radius: 50px; font-weight: 700; font-size: 12px; margin-bottom: 20px; text-transform: uppercase;">
                                📰 NOUVELLE PUBLICATION &bull; {{ $targetTypeLabel ?? 'FAVORI' }}
                            </div>

                            <h2 style="color: #111111; font-size: 22px; font-weight: 700; margin: 0 0 15px 0;">
                                Bonjour {{ $subscriber->name }},
                            </h2>

                            <p style="font-size: 15px; line-height: 1.6; color: #555555; margin-bottom: 25px;">
                                Un nouvel article vient d'être publié dans votre {{ strtolower($targetTypeLabel ?? 'rubrique') }} favorite <strong>« {{ $targetName }} »</strong> sur Tech237 !
                            </p>

                            <!-- Article Card -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; margin-bottom: 25px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.04);">
                                @if(!empty($articleImage))
                                <tr>
                                    <td>
                                        <a href="{{ $articleUrl }}" target="_blank" style="text-decoration: none; display: block;">
                                            <img src="{{ $articleImage }}" alt="{{ $articleTitle }}" width="100%" style="width: 100%; max-height: 280px; object-fit: cover; display: block; border-bottom: 1px solid #e2e8f0;">
                                        </a>
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <td style="padding: 24px;">
                                        @if(!empty($targetName))
                                            <span style="display: inline-block; background-color: #ecfdf5; color: #047857; font-size: 11px; font-weight: 700; text-transform: uppercase; padding: 3px 10px; border-radius: 20px; margin-bottom: 8px;">
                                                {{ $targetName }}
                                            </span>
                                        @endif
                                        <h3 style="margin: 0 0 10px 0; color: #0B2559; font-size: 20px; font-weight: 700; line-height: 1.35;">
                                            <a href="{{ $articleUrl }}" target="_blank" style="color: #0B2559; text-decoration: none;">
                                                {{ $articleTitle }}
                                            </a>
                                        </h3>

                                        @if(!empty($summary))
                                            <p style="color: #475569; font-size: 14px; line-height: 1.6; margin: 12px 0 0 0;">
                                                {{ \Illuminate\Support\Str::limit(strip_tags($summary), 240) }}
                                            </p>
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            <!-- CTA Button -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 30px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $articleUrl }}" target="_blank" style="background-color: #00A859; color: #ffffff; text-decoration: none; padding: 14px 34px; border-radius: 50px; font-weight: 700; font-size: 15px; display: inline-block; box-shadow: 0 4px 15px rgba(0,168,89,0.35);">
                                            Lire l'article complet sur Tech237 &rarr;
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size: 12px; color: #94a3b8; line-height: 1.5; margin-bottom: 0; text-align: center;">
                                Vous recevez cet email car vous suivez <strong>{{ $targetName }}</strong> sur Tech237.<br>
                                <a href="{{ $articleUrl }}" style="color: #00A859; text-decoration: underline;">Lire en ligne</a>
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