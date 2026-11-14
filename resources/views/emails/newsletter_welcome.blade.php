<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tech237 Newsletter</title>
</head>
<body style="margin:0; padding:0; background-color:#f8fafc; font-family: Arial, Helvetica, sans-serif; color:#0f172a;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#f8fafc; padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="max-width:600px; width:100%; background-color:#ffffff; border-radius:16px; overflow:hidden; border:1px solid #e2e8f0;">
                    <tr>
                        <td style="background:linear-gradient(135deg, #0f172a 0%, #dc2626 100%); padding:28px 32px; text-align:center; color:#ffffff;">
                            <h1 style="margin:0; font-size:24px; font-weight:800; letter-spacing:0.5px;">Tech237</h1>
                            <p style="margin:8px 0 0; font-size:13px; opacity:0.9;">Newsletter Officielle / Official Newsletter</p>
                        </td>
                    </tr>

                    <!-- Version Française -->
                    <tr>
                        <td style="padding:28px 32px 16px;">
                            <h2 style="margin:0 0 12px; font-size:18px; color:#dc2626;">🇫🇷 Merci pour votre inscription !</h2>
                            <p style="margin:0 0 12px; font-size:15px; line-height:1.6; color:#334155;">
                                Bonjour,
                            </p>
                            <p style="margin:0 0 12px; font-size:15px; line-height:1.6; color:#334155;">
                                Nous vous remercions chaleureusement pour votre inscription à la newsletter <strong>Tech237</strong> avec l'adresse <strong>{{ $subscriber->email }}</strong>.
                            </p>
                            <p style="margin:0; font-size:15px; line-height:1.6; color:#334155;">
                                Vous recevrez désormais nos meilleurs articles, actualités tech, POCs, projets innovants et opportunités de la communauté.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:8px 32px;">
                            <hr style="border:none; border-top:1px solid #e2e8f0; margin:0;">
                        </td>
                    </tr>

                    <!-- English Version -->
                    <tr>
                        <td style="padding:16px 32px 28px;">
                            <h2 style="margin:0 0 12px; font-size:18px; color:#0f172a;">🇬🇧 Thank you for subscribing!</h2>
                            <p style="margin:0 0 12px; font-size:15px; line-height:1.6; color:#334155;">
                                Hello,
                            </p>
                            <p style="margin:0 0 12px; font-size:15px; line-height:1.6; color:#334155;">
                                Thank you very much for subscribing to the <strong>Tech237</strong> newsletter with <strong>{{ $subscriber->email }}</strong>.
                            </p>
                            <p style="margin:0; font-size:15px; line-height:1.6; color:#334155;">
                                You will now receive our top tech articles, community news, POCs, innovative projects, and exclusive updates.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="background-color:#f1f5f9; padding:18px 32px; text-align:center; font-size:12px; color:#64748b;">
                            © {{ date('Y') }} Tech237 — Tous droits réservés / All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
