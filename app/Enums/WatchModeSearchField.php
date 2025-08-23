<?php
declare(strict_types=1);

namespace App\Enums;

enum WatchModeSearchField: string
{
    case IMDB_ID = 'imdb_id';
    case TMDB_PERSON_ID = 'tmdb_person_id';
    case TMDB_MOVIE_ID = 'tmdb_movie_id';
    case TMDB_TV_ID = 'tmdb_tv_id';
    case NAME = 'name';
}
