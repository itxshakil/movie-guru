<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="x-apple-disable-message-reformatting"/>
    <title>{{ $type === 'weekly' ? 'Weekly' : 'Monthly' }} Movie Digest</title>
    @include('emails.newsletter.partials.styles')
</head>
<body
    style="margin:0;padding:0;background:#f5f5f7;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;-webkit-text-size-adjust:100%;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="mg-outer"
       style="background:#f5f5f7;padding:40px 16px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;">

                @include('emails.newsletter.partials.logo')

                <tr>
                    <td class="mg-card"
                        style="background:#ffffff;border-radius:20px;padding:40px 40px 32px;box-shadow:0 2px 20px rgba(0,0,0,0.06);">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">

                            <tr>
                                <td style="padding:0 0 32px;">
                                    <p style="margin:0 0 8px;font-size:13px;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#86868b;">{{ $type === 'weekly' ? 'Weekly Digest' : 'Monthly Roundup' }}</p>
                                    <h1 class="mg-h1"
                                        style="margin:0 0 12px;font-size:26px;font-weight:700;color:#1d1d1f;letter-spacing:-0.5px;line-height:1.2;">
                                        {{ $type === 'weekly' ? "This week's must-watch films" : "Your monthly cinema roundup" }}&nbsp;{{ $type === 'weekly' ? '🍿' : '📽️' }}
                                    </h1>
                                    <p style="margin:0;font-size:15px;color:#515154;line-height:1.6;">
                                        {{ $type === 'weekly'
                                            ? "We've handpicked the best trending movies and hidden gems for your weekend."
                                            : "The most impactful releases and critically acclaimed hits from the past month." }}
                                    </p>
                                </td>
                            </tr>

                            @if($hiddenGem)
                                <tr>
                                    <td style="padding:0 0 32px;">
                                        @include('emails.newsletter.partials.movie-card', ['movie' => $hiddenGem, 'eyebrow' => '💎 Hidden Gem', 'highlight' => true])
                                    </td>
                                </tr>
                            @endif

                            <tr>
                                <td style="padding:0 0 16px;">
                                    <p style="margin:0;font-size:13px;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#86868b;">
                                        Featured Films</p>
                                </td>
                            </tr>

                            @foreach($movies->take(5) as $movie)
                                <tr>
                                    <td style="padding:0 0 16px;">
                                        @include('emails.newsletter.partials.movie-card', ['movie' => $movie, 'showPlot' => true])
                                    </td>
                                </tr>
                            @endforeach

                            @if($recommendedMovie)
                                <tr>
                                    <td style="padding:16px 0 16px;">
                                        <p style="margin:0 0 12px;font-size:13px;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#86868b;">
                                            🎯 Recommended For You</p>
                                        @include('emails.newsletter.partials.movie-card', ['movie' => $recommendedMovie])
                                    </td>
                                </tr>
                            @endif

                            @if($trendingMovie)
                                <tr>
                                    <td style="padding:16px 0 16px;">
                                        <p style="margin:0 0 12px;font-size:13px;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#86868b;">
                                            🔥 Trending Now</p>
                                        @include('emails.newsletter.partials.movie-card', ['movie' => $trendingMovie])
                                    </td>
                                </tr>
                            @endif

                            <tr>
                                <td align="center" style="padding:16px 0 0;">
                                    <a href="{{ config('app.url') }}?utm_source=newsletter" class="mg-cta"
                                       style="display:inline-block;background:#1d1d1f;color:#ffffff;font-size:15px;font-weight:600;line-height:20px;text-decoration:none;padding:14px 32px;border-radius:980px;letter-spacing:-0.2px;">Browse
                                        All Movies</a>
                                </td>
                            </tr>

                        </table>
                    </td>
                </tr>

                @include('emails.newsletter.partials.footer', ['unsubscribeUrl' => $unsubscribeUrl])

            </table>
        </td>
    </tr>
</table>
</body>
</html>
