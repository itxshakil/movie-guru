@php
    $eyebrow ??= null;
    $showPlot ??= false;
    $highlight ??= false;
    $detailUrl = url('/i/' . $movie->imdb_id);
    $hasPoster = $movie->poster && $movie->poster !== 'N/A';
    $hasRating = $movie->imdb_rating && $movie->imdb_rating !== 'N/A' && (int) $movie->imdb_rating !== 0;
    $plot = $movie->details['Plot'] ?? null;
    $bookingLink = is_array($movie->affiliate_link) ? ($movie->affiliate_link['link'] ?? null) : null;
    $posterWidth = $highlight ? 100 : 90;
    $posterHeight = $highlight ? 150 : 135;
    $cardStyle = $highlight
        ? 'background:#f5f5f7;border-radius:16px;'
        : 'border:1px solid #e8e8ed;border-radius:16px;';
@endphp

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="{{ $cardStyle }}border-collapse:separate;">
    <tr>
        @if($hasPoster)
            <td class="mg-poster" width="{{ $posterWidth }}" valign="top"
                style="width:{{ $posterWidth }}px;padding:0;line-height:0;">
                <a href="{{ $detailUrl }}">
                    <img src="{{ $movie->poster }}" alt="{{ $movie->title }}" width="{{ $posterWidth }}"
                         style="width:{{ $posterWidth }}px;height:{{ $posterHeight }}px;object-fit:cover;display:block;border-radius:15px 0 0 15px;"/>
                </a>
            </td>
        @endif
        <td class="mg-card-body" valign="top" style="padding:16px;">
            @if($eyebrow)
                <p style="margin:0 0 4px;font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#86868b;">{{ $eyebrow }}</p>
            @endif
            <h3 style="margin:0 0 4px;font-size:16px;font-weight:700;color:#1d1d1f;line-height:1.3;">
                <a href="{{ $detailUrl }}" style="color:#1d1d1f;text-decoration:none;">{{ $movie->title }}</a>
            </h3>
            <p style="margin:0 0 10px;font-size:12px;color:#86868b;line-height:1.5;">
                {{ $movie->year }}
                @if($hasRating)
                    · ⭐ {{ $movie->imdb_rating }}
                @endif
                @if($movie->genre)
                    · {{ Str::limit($movie->genre, 20) }}
                @endif
            </p>
            @if($showPlot && $plot && $plot !== 'N/A')
                <p style="margin:0 0 12px;font-size:13px;color:#515154;line-height:1.5;">{{ Str::limit($plot, 100) }}</p>
            @endif
            <a href="{{ $detailUrl }}" class="mg-btn"
               style="display:inline-block;background:#0071e3;color:#ffffff;font-size:13px;font-weight:600;line-height:18px;text-decoration:none;padding:9px 18px;border-radius:980px;margin:0 8px 8px 0;white-space:nowrap;">View Details</a>
            @if($bookingLink)
                <a href="{{ $bookingLink }}?utm_source=newsletter" class="mg-btn"
                   style="display:inline-block;background:#34c759;color:#ffffff;font-size:13px;font-weight:600;line-height:18px;text-decoration:none;padding:9px 18px;border-radius:980px;margin:0 8px 8px 0;white-space:nowrap;">Book Now</a>
            @endif
        </td>
    </tr>
</table>
