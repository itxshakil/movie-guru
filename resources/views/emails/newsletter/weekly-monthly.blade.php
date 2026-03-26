<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>{{ $type === 'weekly' ? 'Weekly' : 'Monthly' }} Movie Digest</title>
</head>
<body
    style="margin:0;padding:0;background:#f5f5f7;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f5f7;padding:40px 16px;">
    <tr>
        <td align="center">
            <table width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;">

                <!-- Logo -->
                <tr>
                    <td align="center" style="padding-bottom:32px;">
                        <img src="{{ config('app.url') }}/icons/ios/64.png" alt="Movie Guru" width="48" height="48"
                             style="border-radius:12px;display:block;margin:0 auto 12px;"/>
                        <span
                            style="font-size:17px;font-weight:700;color:#1d1d1f;letter-spacing:-0.3px;">Movie Guru</span>
                    </td>
                </tr>

                <!-- Hero card -->
                <tr>
                    <td style="background:#ffffff;border-radius:20px 20px 0 0;padding:40px 40px 32px;box-shadow:0 2px 20px rgba(0,0,0,0.06);">
                        <p style="margin:0 0 8px;font-size:13px;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#86868b;">{{ $type === 'weekly' ? 'Weekly Digest' : 'Monthly Roundup' }}</p>
                        <h1 style="margin:0 0 12px;font-size:26px;font-weight:700;color:#1d1d1f;letter-spacing:-0.5px;line-height:1.2;">
                            {{ $type === 'weekly' ? "This week's must-watch films 🍿" : "Your monthly cinema roundup 📽️" }}
                        </h1>
                        <p style="margin:0;font-size:15px;color:#515154;line-height:1.6;">
                            {{ $type === 'weekly'
                                ? "We've handpicked the best trending movies and hidden gems for your weekend."
                                : "The most impactful releases and critically acclaimed hits from the past month." }}
                        </p>
                    </td>
                </tr>

                @if($hiddenGem)
                    <!-- Hidden Gem highlight -->
                    <tr>
                        <td style="background:#ffffff;padding:0 40px 32px;">
                            <table width="100%" cellpadding="0" cellspacing="0"
                                   style="background:#f5f5f7;border-radius:16px;overflow:hidden;">
                                <tr>
                                    @if($hiddenGem->poster && $hiddenGem->poster !== 'N/A')
                                        <td width="100" valign="top" style="padding:0;line-height:0;">
                                            <a href="{{ url('/i/' . $hiddenGem->imdb_id) }}">
                                                <img src="{{ $hiddenGem->poster }}" alt="{{ $hiddenGem->title }}"
                                                     width="100"
                                                     style="width:100px;height:150px;object-fit:cover;display:block;"/>
                                            </a>
                                        </td>
                                    @endif
                                    <td valign="top" style="padding:20px;">
                                        <p style="margin:0 0 4px;font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#86868b;">
                                            💎 Hidden Gem</p>
                                        <h3 style="margin:0 0 6px;font-size:17px;font-weight:700;color:#1d1d1f;">
                                            <a href="{{ url('/i/' . $hiddenGem->imdb_id) }}"
                                               style="color:#1d1d1f;text-decoration:none;">{{ $hiddenGem->title }}</a>
                                        </h3>
                                        <p style="margin:0 0 12px;font-size:13px;color:#86868b;">{{ $hiddenGem->year }} @if($hiddenGem->imdb_rating && $hiddenGem->imdb_rating !== 'N/A')
                                                · ⭐ {{ $hiddenGem->imdb_rating }}
                                            @endif</p>
                                        <a href="{{ url('/i/' . $hiddenGem->imdb_id) }}"
                                           style="display:inline-block;background:#0071e3;color:#ffffff;font-size:13px;font-weight:600;text-decoration:none;padding:10px 20px;border-radius:980px;">View
                                            Details</a>
                                        @if($hiddenGem->affiliate_link && isset($hiddenGem->affiliate_link['link']))
                                            <a href="{{ $hiddenGem->affiliate_link['link'] }}?utm_source=newsletter"
                                               style="display:inline-block;background:#34c759;color:#ffffff;font-size:13px;font-weight:600;text-decoration:none;padding:10px 20px;border-radius:980px;margin-left:8px;">Book
                                                Now</a>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                @endif

                <!-- Movie list -->
                <tr>
                    <td style="background:#ffffff;padding:0 40px 8px;">
                        <p style="margin:0 0 16px;font-size:13px;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#86868b;">
                            Featured Films</p>
                    </td>
                </tr>

                @foreach($movies->take(5) as $movie)
                    <tr>
                        <td style="background:#ffffff;padding:0 40px 20px;">
                            <table width="100%" cellpadding="0" cellspacing="0"
                                   style="border:1px solid #e8e8ed;border-radius:16px;overflow:hidden;">
                                <tr>
                                    @if($movie->poster && $movie->poster !== 'N/A')
                                        <td width="90" valign="top" style="padding:0;line-height:0;">
                                            <a href="{{ url('/i/' . $movie->imdb_id) }}">
                                                <img src="{{ $movie->poster }}" alt="{{ $movie->title }}" width="90"
                                                     style="width:90px;height:135px;object-fit:cover;display:block;"/>
                                            </a>
                                        </td>
                                    @endif
                                    <td valign="top" style="padding:16px;">
                                        <h3 style="margin:0 0 4px;font-size:16px;font-weight:700;color:#1d1d1f;line-height:1.3;">
                                            <a href="{{ url('/i/' . $movie->imdb_id) }}"
                                               style="color:#1d1d1f;text-decoration:none;">{{ $movie->title }}</a>
                                        </h3>
                                        <p style="margin:0 0 10px;font-size:12px;color:#86868b;">
                                            {{ $movie->year }}
                                            @if($movie->imdb_rating && $movie->imdb_rating !== 'N/A')
                                                · ⭐ {{ $movie->imdb_rating }}
                                            @endif
                                            @if($movie->genre)
                                                · {{ Str::limit($movie->genre, 20) }}
                                            @endif
                                        </p>
                                        @if(isset($movie->details['Plot']) && $movie->details['Plot'] !== 'N/A')
                                            <p style="margin:0 0 12px;font-size:13px;color:#515154;line-height:1.5;">{{ Str::limit($movie->details['Plot'], 100) }}</p>
                                        @endif
                                        <a href="{{ url('/i/' . $movie->imdb_id) }}"
                                           style="display:inline-block;background:#0071e3;color:#ffffff;font-size:12px;font-weight:600;text-decoration:none;padding:8px 18px;border-radius:980px;">View
                                            Details →</a>
                                        @if($movie->affiliate_link && isset($movie->affiliate_link['link']))
                                            <a href="{{ $movie->affiliate_link['link'] }}?utm_source=newsletter"
                                               style="display:inline-block;background:#34c759;color:#ffffff;font-size:12px;font-weight:600;text-decoration:none;padding:8px 18px;border-radius:980px;margin-left:6px;">Book
                                                Now</a>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                @endforeach

                @if($recommendedMovie)
                    <!-- Recommended Movie -->
                    <tr>
                        <td style="background:#ffffff;padding:0 40px 20px;">
                            <p style="margin:0 0 12px;font-size:13px;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#86868b;">
                                🎯 Recommended For You</p>
                            <table width="100%" cellpadding="0" cellspacing="0"
                                   style="border:1px solid #e8e8ed;border-radius:16px;overflow:hidden;">
                                <tr>
                                    @if($recommendedMovie->poster && $recommendedMovie->poster !== 'N/A')
                                        <td width="90" valign="top" style="padding:0;line-height:0;">
                                            <a href="{{ url('/i/' . $recommendedMovie->imdb_id) }}"><img
                                                    src="{{ $recommendedMovie->poster }}"
                                                    alt="{{ $recommendedMovie->title }}" width="90"
                                                    style="width:90px;height:135px;object-fit:cover;display:block;"/></a>
                                        </td>
                                    @endif
                                    <td valign="top" style="padding:16px;">
                                        <h3 style="margin:0 0 4px;font-size:16px;font-weight:700;color:#1d1d1f;"><a
                                                href="{{ url('/i/' . $recommendedMovie->imdb_id) }}"
                                                style="color:#1d1d1f;text-decoration:none;">{{ $recommendedMovie->title }}</a>
                                        </h3>
                                        <p style="margin:0 0 10px;font-size:12px;color:#86868b;">{{ $recommendedMovie->year }}@if($recommendedMovie->imdb_rating && $recommendedMovie->imdb_rating !== 'N/A')
                                                · ⭐ {{ $recommendedMovie->imdb_rating }}
                                            @endif</p>
                                        <a href="{{ url('/i/' . $recommendedMovie->imdb_id) }}"
                                           style="display:inline-block;background:#0071e3;color:#ffffff;font-size:12px;font-weight:600;text-decoration:none;padding:8px 18px;border-radius:980px;">View
                                            Details →</a>
                                        @if($recommendedMovie->affiliate_link && isset($recommendedMovie->affiliate_link['link']))
                                            <a href="{{ $recommendedMovie->affiliate_link['link'] }}?utm_source=newsletter"
                                               style="display:inline-block;background:#34c759;color:#ffffff;font-size:12px;font-weight:600;text-decoration:none;padding:8px 18px;border-radius:980px;margin-left:6px;">Book
                                                Now</a>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                @endif

                @if($trendingMovie)
                    <!-- Trending Movie -->
                    <tr>
                        <td style="background:#ffffff;padding:0 40px 20px;">
                            <p style="margin:0 0 12px;font-size:13px;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#86868b;">
                                🔥 Trending Now</p>
                            <table width="100%" cellpadding="0" cellspacing="0"
                                   style="border:1px solid #e8e8ed;border-radius:16px;overflow:hidden;">
                                <tr>
                                    @if($trendingMovie->poster && $trendingMovie->poster !== 'N/A')
                                        <td width="90" valign="top" style="padding:0;line-height:0;">
                                            <a href="{{ url('/i/' . $trendingMovie->imdb_id) }}"><img
                                                    src="{{ $trendingMovie->poster }}" alt="{{ $trendingMovie->title }}"
                                                    width="90"
                                                    style="width:90px;height:135px;object-fit:cover;display:block;"/></a>
                                        </td>
                                    @endif
                                    <td valign="top" style="padding:16px;">
                                        <h3 style="margin:0 0 4px;font-size:16px;font-weight:700;color:#1d1d1f;"><a
                                                href="{{ url('/i/' . $trendingMovie->imdb_id) }}"
                                                style="color:#1d1d1f;text-decoration:none;">{{ $trendingMovie->title }}</a>
                                        </h3>
                                        <p style="margin:0 0 10px;font-size:12px;color:#86868b;">{{ $trendingMovie->year }}@if($trendingMovie->imdb_rating && $trendingMovie->imdb_rating !== 'N/A')
                                                · ⭐ {{ $trendingMovie->imdb_rating }}
                                            @endif</p>
                                        <a href="{{ url('/i/' . $trendingMovie->imdb_id) }}"
                                           style="display:inline-block;background:#0071e3;color:#ffffff;font-size:12px;font-weight:600;text-decoration:none;padding:8px 18px;border-radius:980px;">View
                                            Details →</a>
                                        @if($trendingMovie->affiliate_link && isset($trendingMovie->affiliate_link['link']))
                                            <a href="{{ $trendingMovie->affiliate_link['link'] }}?utm_source=newsletter"
                                               style="display:inline-block;background:#34c759;color:#ffffff;font-size:12px;font-weight:600;text-decoration:none;padding:8px 18px;border-radius:980px;margin-left:6px;">Book
                                                Now</a>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                @endif

                <!-- CTA -->
                <tr>
                    <td style="background:#ffffff;border-radius:0 0 20px 20px;padding:8px 40px 40px;box-shadow:0 2px 20px rgba(0,0,0,0.06);">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td align="center">
                                    <a href="{{ config('app.url') }}"
                                       style="display:inline-block;background:#1d1d1f;color:#ffffff;font-size:15px;font-weight:600;text-decoration:none;padding:14px 32px;border-radius:980px;letter-spacing:-0.2px;">Browse
                                        All Movies</a>
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
                            @if($unsubscribeUrl)
                                <a href="{{ $unsubscribeUrl }}" style="color:#86868b;text-decoration:underline;">Unsubscribe</a>
                            @endif
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>
</body>
</html>
