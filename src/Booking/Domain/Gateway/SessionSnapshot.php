<?php

declare(strict_types=1);

namespace App\Booking\Domain\Gateway;

use App\Catalog\Domain\Model\SessionId;
use App\Shared\Domain\ValueObject\Money;
use DateTimeImmutable;

final class SessionSnapshot
{
    public function __construct(
        public readonly SessionId $id,
        public readonly DateTimeImmutable $date,
        public readonly Money $pricePerSeat,
    ) {}
}