<?php

declare(strict_types=1);

namespace App\Booking\Domain\Notification;

use App\Booking\Domain\Model\Reservation;

interface ReservationNotifierInterface
{
    public function notifyReservationCreated(Reservation $reservation): void;

    public function notifyReservationCancelled(Reservation $reservation): void;
}