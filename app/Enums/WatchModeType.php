<?php
declare(strict_types=1);

namespace App\Enums;

enum WatchModeType: string
{
    case TV = 'tv';
    case MOVIE = 'movie';
    case PERSON = 'person';
}
