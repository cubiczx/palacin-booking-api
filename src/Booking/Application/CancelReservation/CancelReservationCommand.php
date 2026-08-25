<?php

declare(strict_types=1);

namespace App\Booking\Application\CancelReservation;

final class CancelReservationCommand
{
    public function __construct(public readonly string $reservationId) {}
}