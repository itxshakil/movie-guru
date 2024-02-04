<?php

namespace App\OMDB;

enum MovieType: string
{
    case Movie = 'movie';
    case Series = 'series';
    case Episode = 'episode';
}
