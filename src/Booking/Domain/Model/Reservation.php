<?php

declare(strict_types=1);

namespace App\Booking\Domain\Model;

use App\Booking\Domain\Exception\CancellationWindowExceededException;
use App\Booking\Domain\Exception\ReservationAlreadyCancelledException;
use App\Booking\Domain\Model\ReservationId;
use App\Catalog\Domain\Model\SessionId;
use App\Booking\Domain\Model\UserId;
use App\Shared\Domain\ValueObject\Money;
use DateTimeImmutable;

final class Reservation
{
    private function __construct(
        private readonly ReservationId $id,
        private readonly SessionId $sessionId,
        private readonly UserId $userId,
        private readonly int $seats,
        private readonly Money $totalPrice,
        private readonly string $contactEmail,
        private ReservationStatus $status,
        private readonly DateTimeImmutable $createdAt,
    ) {}

    public static function create(
        ReservationId $id,
        SessionId $sessionId,
        UserId $userId,
        int $seats,
        Money $totalPrice,
        string $contactEmail,
        DateTimeImmutable $now,
    ): self {
        if ($seats <= 0) {
            throw new \InvalidArgumentException('Seats must be a positive number.');
        }

        return new self($id, $sessionId, $userId, $seats, $totalPrice, $contactEmail, ReservationStatus::CONFIRMED, $now);
    }

    public function cancel(DateTimeImmutable $sessionDate, DateTimeImmutable $now): void
    {
        if ($this->status === ReservationStatus::CANCELLED) {
            throw new ReservationAlreadyCancelledException('This reservation is already cancelled.');
        }

        if ($now >= $sessionDate->modify('-24 hours')) {
            throw new CancellationWindowExceededException(
                'Reservations cannot be cancelled less than 24 hours before the session starts.'
            );
        }

        $this->status = ReservationStatus::CANCELLED;
    }

    public function id(): ReservationId { return $this->id; }
    public function sessionId(): SessionId { return $this->sessionId; }
    public function userId(): UserId { return $this->userId; }
    public function seats(): int { return $this->seats; }
    public function totalPrice(): Money { return $this->totalPrice; }
    public function contactEmail(): string { return $this->contactEmail; }
    public function status(): ReservationStatus { return $this->status; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }
}