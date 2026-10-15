<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Trailer - {{ config('app.name', 'Movie Guru') }}</title>
    <style>
        html, body { margin: 0; height: 100%; background: #000; overflow: hidden; }
        iframe { display: block; width: 100%; height: 100%; border: 0; }
    </style>
</head>
<body>
    <iframe
        src="{{ $embedUrl }}"
        title="Trailer"
        allow="autoplay; encrypted-media; picture-in-picture; fullscreen"
        referrerpolicy="strict-origin-when-cross-origin"
        allowfullscreen
    ></iframe>
</body>
</html>
