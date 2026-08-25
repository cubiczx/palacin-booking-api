<?php

declare(strict_types=1);

namespace App\Booking\Application\ReserveSeats;

final class ReserveSeatsCommand
{
    public function __construct(
        public readonly string $sessionId,
        public readonly string $userId,
        public readonly int $seats,
        public readonly string $contactEmail,
    ) {}
}