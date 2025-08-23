<?php
declare(strict_types=1);

namespace App\Enums;

enum WatchModeSourceType: string
{
    case SUBSCRIPTION = 'sub';
    case FREE = 'free';
    case PURCHASE = 'purchase';
    case BUY = 'buy';       // Purchase (digital copy)
    case RENTAL = 'rent';        // Rental
    case TV_EVERYWHERE = 'tve';  // Requires cable subscription login
}
