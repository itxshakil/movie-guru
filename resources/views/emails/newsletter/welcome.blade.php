<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Welcome to Movie Guru</title>
</head>
<body
    style="margin:0;padding:0;background:#f5f5f7;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f5f7;padding:40px 16px;">
    <tr>
        <td align="center">
            <table width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;">

                <!-- Logo -->
                <tr>
                    <td align="center" style="padding-bottom:32px;">
                        <img src="{{ config('app.url') }}/icons/ios/64.png" alt="Movie Guru" width="48" height="48"
                             style="border-radius:12px;display:block;margin:0 auto 12px;"/>
                        <span
                            style="font-size:17px;font-weight:700;color:#1d1d1f;letter-spacing:-0.3px;">Movie Guru</span>
                    </td>
                </tr>

                <!-- Card -->
                <tr>
                    <td style="background:#ffffff;border-radius:20px;padding:40px 40px 32px;box-shadow:0 2px 20px rgba(0,0,0,0.06);">

                        <p style="margin:0 0 8px;font-size:13px;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#86868b;">
                            Welcome</p>
                        <h1 style="margin:0 0 16px;font-size:28px;font-weight:700;color:#1d1d1f;letter-spacing:-0.5px;line-height:1.2;">
                            Hi {{ $firstName }}, great to have you. 🎬</h1>
                        <p style="margin:0 0 28px;font-size:16px;color:#515154;line-height:1.6;">You've joined a
                            community of movie lovers who never run out of great things to watch. Every week we'll bring
                            you curated picks, hidden gems, and fresh releases — straight to your inbox.</p>

                        <!-- Features -->
                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:32px;">
                            <tr>
                                <td style="padding:12px 16px;background:#f5f5f7;border-radius:12px;margin-bottom:8px;display:block;">
                                    <span style="font-size:15px;color:#1d1d1f;">🎥 <strong>Curated picks</strong> — hidden gems, classics, and new releases</span>
                                </td>
                            </tr>
                            <tr>
                                <td style="height:8px;"></td>
                            </tr>
                            <tr>
                                <td style="padding:12px 16px;background:#f5f5f7;border-radius:12px;">
                                    <span style="font-size:15px;color:#1d1d1f;">⭐ <strong>Trending now</strong> — what everyone's watching this week</span>
                                </td>
                            </tr>
                            <tr>
                                <td style="height:8px;"></td>
                            </tr>
                            <tr>
                                <td style="padding:12px 16px;background:#f5f5f7;border-radius:12px;">
                                    <span style="font-size:15px;color:#1d1d1f;">💎 <strong>Hidden gems</strong> — underrated films you'll love</span>
                                </td>
                            </tr>
                        </table>

                        <!-- CTA -->
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td align="center">
                                    <a href="{{ config('app.url') }}"
                                       style="display:inline-block;background:#0071e3;color:#ffffff;font-size:15px;font-weight:600;text-decoration:none;padding:14px 32px;border-radius:980px;letter-spacing:-0.2px;">Browse
                                        Movies Now</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td align="center" style="padding:28px 0 0;">
                        <p style="margin:0;font-size:12px;color:#86868b;line-height:1.6;">
                            You're receiving this because you subscribed at Movie Guru.<br/>
                            <a href="{{ \Illuminate\Support\Facades\URL::signedRoute('unsubscribe', ['email' => $email]) }}"
                               style="color:#86868b;text-decoration:underline;">Unsubscribe</a>
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>
</body>
</html>
