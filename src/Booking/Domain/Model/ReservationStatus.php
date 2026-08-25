<?php

declare(strict_types=1);

namespace App\Booking\Domain\Model;

enum ReservationStatus: string
{
    case CONFIRMED = 'confirmed';
    case CANCELLED = 'cancelled';
}