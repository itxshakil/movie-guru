<?php

declare(strict_types=1);

use App\Services\TitleCleaner;

it('cleans full movie titles correctly', function () {
    $cleaner = new TitleCleaner();

    expect($cleaner->clean('Inception Full Movie'))
        ->toBe('Inception')
        ->and($cleaner->clean('Baahubali South Movie'))
        ->toBe('Baahubali')
        ->and($cleaner->clean('Avatar Full-Movie'))
        ->toBe('Avatar')
        ->and($cleaner->clean('Spider-Man (2023) Full Movie'))
        ->toBe('Spider-Man')
        ->and($cleaner->clean('   The Matrix FULL MOVIE   '))
        ->toBe('The Matrix');
});

it('handles various platform and quality names', function () {
    $cleaner = new TitleCleaner();

    expect($cleaner->clean('Matrix 4 Netflix HDRip'))
        ->toBe('Matrix 4')
        ->and($cleaner->clean('The Godfather 1080p Prime Video'))
        ->toBe('The Godfather')
        ->and($cleaner->clean('Frozen CamRip Hotstar'))
        ->toBe('Frozen')
        ->and($cleaner->clean('Titanic VHS'))
        ->toBe('Titanic');
});

it('removes languages and subtitles correctly', function () {
    $cleaner = new TitleCleaner();

    expect($cleaner->clean('Dangal Hindi Dubbed'))
        ->toBe('Dangal');
    expect($cleaner->clean('RRR Tamil Subtitle'))
        ->toBe('RRR');
    expect($cleaner->clean('Parasite English Subtitles'))
        ->toBe('Parasite');
    expect($cleaner->clean('Drishyam 2 Malayalam'))
        ->toBe('Drishyam 2');
});

it('removes redundant or repetitive information', function () {
    $cleaner = new TitleCleaner();

//    expect($cleaner->clean('The Avengers The Avengers'))
//        ->toBe('The Avengers');
    expect($cleaner->clean('Toy Story 1995 1995'))
        ->toBe('Toy Story 1995');
//    expect($cleaner->clean('Classic Classic Remake'))
//        ->toBe('Remake');
    expect($cleaner->clean('The The Matrix Matrix'))
        ->toBe('The Matrix');
});

it('handles edge cases and preserves original title when needed', function () {
    $cleaner = new TitleCleaner();

    expect($cleaner->clean(null))
        ->toBe('');
    expect($cleaner->clean('A'))
        ->toBe('A');
    expect($cleaner->clean('  '))
        ->toBe('  ');
    expect($cleaner->clean('Titanic'))
        ->toBe('Titanic');
});
