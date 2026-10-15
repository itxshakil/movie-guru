<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\TrailerEmbedRequest;
use Illuminate\Http\Response;

/**
 * Hosts the YouTube embed for the trailer Picture-in-Picture window. That window's document is
 * about:blank, and YouTube rejects embeds requested without a referrer (Error 153), so the
 * window loads this same-origin page, which embeds the player with our origin as referrer.
 */
final class TrailerEmbedController extends Controller
{
    public function show(TrailerEmbedRequest $request, string $videoId): Response
    {
        $embedParameters = ['autoplay' => 1, 'rel' => 0, 'modestbranding' => 1];
        if ($request->integer('start') > 0) {
            $embedParameters['start'] = $request->integer('start');
        }

        return response()
            ->view('trailer-embed', [
                'embedUrl' => 'https://www.youtube-nocookie.com/embed/' . $videoId . '?' . http_build_query($embedParameters),
            ])
            ->header('Content-Security-Policy', "frame-ancestors 'self'");
    }
}
