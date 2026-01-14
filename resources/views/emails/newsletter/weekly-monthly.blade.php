<x-mail::message>
    <h1 style="color: #0f172a; font-size: 26px; font-weight: 800; margin-bottom: 8px;">
        🎬 {{ $type === 'weekly' ? 'Weekly' : 'Monthly' }} Movie Digest</h1>

    <p style="font-size: 16px; color: #475569; margin-bottom: 24px;">
        Hi Movie Lover! 👋 <br><br>
        @if($type === 'weekly')
            We've handpicked this week's <strong>must-watch trending movies</strong> and discovered some <strong>hidden
                gems</strong> just for you. Get ready for a perfect movie weekend! 🍿
        @else
            Your monthly cinema roundup is here! We've analyzed the most <strong>impactful releases</strong> and
            <strong>critically acclaimed</strong> hits from the past month. 📽️
        @endif
    </p>

    <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0; border-collapse: separate;">
        <tr>
            <td>
                @foreach($movies as $movie)
                    <table class="movie-card" width="100%" cellpadding="0" cellspacing="0"
                           style="margin-bottom: 24px; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; background-color: #ffffff; border-collapse: separate;">
                        <tr>
                            <td width="130" valign="top" style="padding: 0; line-height: 0;">
                                <a href="{{ url('/i/' . $movie->imdb_id) }}">
                                    <img src="{{ $movie->poster }}" alt="{{ $movie->title }}" width="130" height="190"
                                         style="width: 130px; height: 190px; object-fit: cover; display: block; border: none;">
                                </a>
                            </td>
                            <td valign="top" style="padding: 20px;">
                                <h2 class="movie-title"
                                    style="margin: 0 0 8px 0; font-size: 20px; color: #1e293b; font-weight: 800;">
                                    <a href="{{ url('/i/' . $movie->imdb_id) }}"
                                       style="color: #1e293b; text-decoration: none;">{{ $movie->title }}</a>
                                </h2>
                                <div class="movie-meta" style="margin-bottom: 12px; font-size: 14px; font-weight: 600;">
                                    <span style="color: #eab308;">⭐ {{ $movie->imdb_rating }}</span>
                                    <span style="margin: 0 10px; color: #cbd5e1;">|</span>
                                    <span style="color: #64748b;" class="movie-meta">📅 {{ $movie->year }}</span>
                                    <span style="margin: 0 10px; color: #cbd5e1;">|</span>
                                    <span style="color: #64748b;"
                                          class="movie-meta">🎭 {{ Str::limit($movie->genre, 15) }}</span>
                                </div>
                                <p class="movie-plot"
                                   style="margin: 0 0 16px 0; font-size: 14px; line-height: 1.6; color: #475569;">
                                    {{ Str::limit($movie->details['Plot'] ?? 'No description available.', 130) }}
                                </p>
                                <table border="0" cellpadding="0" cellspacing="0" role="presentation">
                                    <tr>
                                        <td align="center" bgcolor="#3b82f6" style="border-radius: 8px;">
                                            <a href="{{ url('/i/' . $movie->imdb_id) }}"
                                               style="font-size: 13px; font-weight: 700; color: #ffffff; text-decoration: none; padding: 8px 16px; border-radius: 8px; display: inline-block; border: 1px solid #3b82f6;">View
                                                Details →</a>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                @endforeach
            </td>
        </tr>
    </table>

    @if($recommendedMovie || $hiddenGem)
        <table class="special-selections-container" width="100%" cellpadding="0" cellspacing="0"
               style="margin: 40px 0; background-color: #f1f5f9; border-radius: 20px; border: 1px solid #e2e8f0; border-collapse: separate;">
            <tr>
                <td style="padding: 30px;">
                    <h2 style="margin: 0 0 24px 0; font-size: 24px; color: #0f172a; text-align: center; font-weight: 800;">
                        ✨ Special Selections</h2>

                    @if($recommendedMovie)
                        <table class="special-card" width="100%" cellpadding="0" cellspacing="0"
                               style="margin-bottom: 24px; background-color: #ffffff; border-radius: 16px; border-left: 5px solid #eab308; border-collapse: separate; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                            <tr>
                                <td style="padding: 20px;">
                                    <h3 style="margin: 0 0 15px 0; color: #1e293b; font-size: 18px; font-weight: 800;">
                                        🌟 Recommended for You</h3>
                                    <table width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td width="90" valign="top" style="padding-right: 20px;">
                                                <a href="{{ url('/i/' . $recommendedMovie->imdb_id) }}">
                                                    <img src="{{ $recommendedMovie->poster }}"
                                                         alt="{{ $recommendedMovie->title }}" width="90" height="130"
                                                         style="width: 90px; height: 130px; border-radius: 8px; object-fit: cover; display: block; border: none;">
                                                </a>
                                            </td>
                                            <td valign="top">
                                                <h4 class="movie-title"
                                                    style="margin: 0 0 6px 0; font-size: 17px; font-weight: 800; color: #1e293b;">
                                                    <a href="{{ url('/i/' . $recommendedMovie->imdb_id) }}"
                                                       style="color: #1e293b; text-decoration: none;">{{ $recommendedMovie->title }}</a>
                                                </h4>
                                                <div class="movie-meta"
                                                     style="margin-bottom: 8px; font-size: 13px; font-weight: 600; color: #64748b;">
                                                    <span>⭐ {{ $recommendedMovie->imdb_rating }}</span> • <span
                                                        class="movie-meta">{{ $recommendedMovie->year }}</span>
                                                </div>
                                                <p class="movie-plot-special"
                                                   style="margin: 0 0 12px 0; font-size: 14px; color: #475569; line-height: 1.5;">{{ Str::limit($recommendedMovie->details['Plot'] ?? '', 110) }}</p>
                                                <table border="0" cellpadding="0" cellspacing="0" role="presentation">
                                                    <tr>
                                                        <td align="center" bgcolor="#3b82f6"
                                                            style="border-radius: 6px;">
                                                            <a href="{{ url('/i/' . $recommendedMovie->imdb_id) }}"
                                                               style="font-size: 12px; font-weight: 700; color: #ffffff; text-decoration: none; padding: 6px 12px; border-radius: 6px; display: inline-block; border: 1px solid #3b82f6;">View
                                                                Details →</a>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    @endif

                    @if($hiddenGem)
                        <table class="special-card" width="100%" cellpadding="0" cellspacing="0"
                               style="background-color: #ffffff; border-radius: 16px; border-left: 5px solid #8b5cf6; border-collapse: separate; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                            <tr>
                                <td style="padding: 20px;">
                                    <h3 style="margin: 0 0 15px 0; color: #1e293b; font-size: 18px; font-weight: 800;">
                                        💎 Hidden Gem</h3>
                                    <table width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td width="90" valign="top" style="padding-right: 20px;">
                                                <a href="{{ url('/i/' . $hiddenGem->imdb_id) }}">
                                                    <img src="{{ $hiddenGem->poster }}" alt="{{ $hiddenGem->title }}"
                                                         width="90" height="130"
                                                         style="width: 90px; height: 130px; border-radius: 8px; object-fit: cover; display: block; border: none;">
                                                </a>
                                            </td>
                                            <td valign="top">
                                                <h4 class="movie-title"
                                                    style="margin: 0 0 6px 0; font-size: 17px; font-weight: 800; color: #1e293b;">
                                                    <a href="{{ url('/i/' . $hiddenGem->imdb_id) }}"
                                                       style="color: #1e293b; text-decoration: none;">{{ $hiddenGem->title }}</a>
                                                </h4>
                                                <div class="movie-meta"
                                                     style="margin-bottom: 8px; font-size: 13px; font-weight: 600; color: #64748b;">
                                                    <span>⭐ {{ $hiddenGem->imdb_rating }}</span> • <span
                                                        class="movie-meta">{{ $hiddenGem->year }}</span>
                                                </div>
                                                <p class="movie-plot-special"
                                                   style="margin: 0 0 12px 0; font-size: 14px; color: #475569; line-height: 1.5;">{{ Str::limit($hiddenGem->details['Plot'] ?? '', 110) }}</p>
                                                <table border="0" cellpadding="0" cellspacing="0" role="presentation">
                                                    <tr>
                                                        <td align="center" bgcolor="#3b82f6"
                                                            style="border-radius: 6px;">
                                                            <a href="{{ url('/i/' . $hiddenGem->imdb_id) }}"
                                                               style="font-size: 12px; font-weight: 700; color: #ffffff; text-decoration: none; padding: 6px 12px; border-radius: 6px; display: inline-block; border: 1px solid #3b82f6;">View
                                                                Details →</a>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    @endif
                </td>
            </tr>
        </table>
    @endif

    <table width="100%" cellpadding="0" cellspacing="0"
           style="text-align: center; margin: 40px 0; border-collapse: separate;">
        <tr>
            <td>
                <x-mail::button :url="config('app.url')" color="primary">
                    Explore More Movies
                </x-mail::button>
            </td>
        </tr>
    </table>

    <p style="font-size: 15px; color: #475569;">
        Best regards,<br>
        <strong style="color: #0f172a;" class="footer-text">The {{ config('app.name') }} Team</strong> 🎬
    </p>

    <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 30px 0;">

    @php
        $finalUnsubscribeUrl = $unsubscribeUrl ?? url('/unsubscribe?email=' . ($email ?? ''));
    @endphp

    <table width="100%" cellpadding="0" cellspacing="0"
           style="text-align: center; margin-top: 20px; border-collapse: separate;">
        <tr>
            <td style="font-size: 12px; color: #94a3b8; line-height: 1.5;">
                You're receiving this because you subscribed to Movie Guru. <br>
                <a href="{{ $finalUnsubscribeUrl }}" style="color: #3b82f6; text-decoration: underline;">Unsubscribe</a>
            </td>
        </tr>
    </table>
</x-mail::message>
