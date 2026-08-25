<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Model;

use App\Catalog\Domain\Exception\InvalidCapacityException;
use App\Catalog\Domain\Exception\PastSessionDateException;
use App\Shared\Domain\ValueObject\Money;
use DateTimeImmutable;

final class Session
{
    private function __construct(
        private readonly SessionId $id,
        private readonly ExperienceId $experienceId,
        private readonly DateTimeImmutable $date,
        private readonly int $capacity,
        private int $availableSeats,
        private readonly Money $price,
    ) {}

    public static function create(
        SessionId $id,
        ExperienceId $experienceId,
        DateTimeImmutable $date,
        int $capacity,
        Money $price,
        DateTimeImmutable $now,
    ): self {
        if ($capacity <= 0) {
            throw new InvalidCapacityException('Session capacity must be greater than zero.');
        }

        if ($date < $now) {
            throw new PastSessionDateException('Cannot create a session in the past.');
        }

        return new self($id, $experienceId, $date, $capacity, $capacity, $price);
    }

    public function id(): SessionId
    {
        return $this->id;
    }

    public function experienceId(): ExperienceId
    {
        return $this->experienceId;
    }

    public function date(): DateTimeImmutable
    {
        return $this->date;
    }

    public function capacity(): int
    {
        return $this->capacity;
    }

    public function availableSeats(): int
    {
        return $this->availableSeats;
    }

    public function price(): Money
    {
        return $this->price;
    }

    public function hasStarted(DateTimeImmutable $now): bool
    {
        return $now >= $this->date;
    }

    /**
     * Expresses the business invariant at the domain level (used in unit tests).
     * The real guarantee against race conditions is applied in the infrastructure gateway
     * through a conditional atomic UPDATE — see
     * DoctrineSessionAvailabilityGateway.
     */
    public function reserve(int $seats): void
    {
        if ($seats > $this->availableSeats) {
            throw new \DomainException('Not enough seats available.');
        }

        $this->availableSeats -= $seats;
    }

    public function release(int $seats): void
    {
        $this->availableSeats = min($this->capacity, $this->availableSeats + $seats);
    }
}