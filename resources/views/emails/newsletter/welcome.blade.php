<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="x-apple-disable-message-reformatting"/>
    <title>Welcome to Movie Guru</title>
    @include('emails.newsletter.partials.styles')
</head>
<body
    style="margin:0;padding:0;background:#f5f5f7;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;-webkit-text-size-adjust:100%;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="mg-outer"
       style="background:#f5f5f7;padding:40px 16px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;">

                @include('emails.newsletter.partials.logo')

                <!-- Card -->
                <tr>
                    <td class="mg-card"
                        style="background:#ffffff;border-radius:20px;padding:40px 40px 32px;box-shadow:0 2px 20px rgba(0,0,0,0.06);">

                        <p style="margin:0 0 8px;font-size:13px;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#86868b;">
                            Welcome</p>
                        <h1 class="mg-h1" style="margin:0 0 16px;font-size:28px;font-weight:700;color:#1d1d1f;letter-spacing:-0.5px;line-height:1.2;">
                            Hi {{ $firstName }}, great to have you.&nbsp;🎬</h1>
                        <p style="margin:0 0 28px;font-size:16px;color:#515154;line-height:1.6;">You've joined a
                            community of movie lovers who never run out of great things to watch. Every week we'll bring
                            you curated picks, hidden gems, and fresh releases - straight to your inbox.</p>

                        <!-- Features -->
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:32px;">
                            <tr>
                                <td style="padding:12px 16px;background:#f5f5f7;border-radius:12px;">
                                    <span style="font-size:15px;color:#1d1d1f;line-height:1.5;">🎥 <strong>Curated picks</strong> - hidden gems, classics, and new releases</span>
                                </td>
                            </tr>
                            <tr>
                                <td style="height:8px;"></td>
                            </tr>
                            <tr>
                                <td style="padding:12px 16px;background:#f5f5f7;border-radius:12px;">
                                    <span style="font-size:15px;color:#1d1d1f;line-height:1.5;">⭐ <strong>Trending now</strong> - what everyone's watching this week</span>
                                </td>
                            </tr>
                            <tr>
                                <td style="height:8px;"></td>
                            </tr>
                            <tr>
                                <td style="padding:12px 16px;background:#f5f5f7;border-radius:12px;">
                                    <span style="font-size:15px;color:#1d1d1f;line-height:1.5;">💎 <strong>Hidden gems</strong> - underrated films you'll love</span>
                                </td>
                            </tr>
                        </table>

                        <!-- CTA -->
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td align="center">
                                    <a href="{{ config('app.url') }}" class="mg-cta"
                                       style="display:inline-block;line-height:20px;background:#0071e3;color:#ffffff;font-size:15px;font-weight:600;text-decoration:none;padding:14px 32px;border-radius:980px;letter-spacing:-0.2px;">Browse
                                        Movies Now</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                @include('emails.newsletter.partials.footer', ['unsubscribeUrl' => \Illuminate\Support\Facades\URL::signedRoute('unsubscribe', ['email' => $email])])

            </table>
        </td>
    </tr>
</table>
</body>
</html>
