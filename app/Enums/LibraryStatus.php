<?php

declare(strict_types=1);

namespace App\Enums;

enum LibraryStatus: string
{
    case ToWatch = 'to_watch';
    case Watched = 'watched';
}
