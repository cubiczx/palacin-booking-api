<?php

declare(strict_types=1);

namespace App\Booking\Domain\Repository;

use App\Booking\Domain\Model\Reservation;
use App\Booking\Domain\Model\ReservationId;

interface ReservationRepositoryInterface
{
    public function save(Reservation $reservation): void;

    public function ofId(ReservationId $id): ?Reservation;
}