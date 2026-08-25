<?php

declare(strict_types=1);

namespace App\Booking\Domain\Gateway;

use App\Catalog\Domain\Model\SessionId;

interface SessionAvailabilityGatewayInterface
{
    public function findSession(SessionId $sessionId): ?SessionSnapshot;

    /**
     * Reserve `$seats` seats atomically at the database level.
     * Returns false if there is not enough capacity at the time of the operation,
     * ensuring robustness against massive concurrent reservations.
     */
    public function tryReserveSeats(SessionId $sessionId, int $seats): bool;

    public function releaseSeats(SessionId $sessionId, int $seats): void;
}