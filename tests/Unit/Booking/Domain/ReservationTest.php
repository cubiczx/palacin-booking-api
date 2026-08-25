<?php

declare(strict_types=1);

namespace App\Tests\Unit\Booking\Domain;

use App\Booking\Domain\Exception\CancellationWindowExceededException;
use App\Booking\Domain\Exception\ReservationAlreadyCancelledException;
use App\Booking\Domain\Model\Reservation;
use App\Booking\Domain\Model\ReservationId;
use App\Booking\Domain\Model\ReservationStatus;
use App\Booking\Domain\Model\UserId;
use App\Catalog\Domain\Model\SessionId;
use App\Shared\Domain\ValueObject\Money;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ReservationTest extends TestCase
{
    public function test_reservation_can_be_cancelled_before_the_24h_window(): void
    {
        $now = new DateTimeImmutable('2026-08-25 10:00:00');
        $sessionDate = new DateTimeImmutable('2026-08-27 10:00:00');

        $reservation = Reservation::create(
            ReservationId::generate(), SessionId::generate(), new UserId('user-1'),
            2, Money::fromCents(2000), 'client@example.com', $now,
        );

        $reservation->cancel($sessionDate, $now);

        self::assertSame(ReservationStatus::CANCELLED, $reservation->status());
    }

    public function test_reservation_cannot_be_cancelled_less_than_24h_before_session(): void
    {
        $now = new DateTimeImmutable('2026-08-26 11:00:00');
        $sessionDate = new DateTimeImmutable('2026-08-27 10:00:00'); // < 24h left

        $reservation = Reservation::create(
            ReservationId::generate(), SessionId::generate(), new UserId('user-1'),
            2, Money::fromCents(2000), 'client@example.com', $now,
        );

        $this->expectException(CancellationWindowExceededException::class);
        $reservation->cancel($sessionDate, $now);
    }

    public function test_a_cancelled_reservation_cannot_be_cancelled_again(): void
    {
        $now = new DateTimeImmutable('2026-08-20 10:00:00');
        $sessionDate = new DateTimeImmutable('2026-08-27 10:00:00');

        $reservation = Reservation::create(
            ReservationId::generate(), SessionId::generate(), new UserId('user-1'),
            2, Money::fromCents(2000), 'client@example.com', $now,
        );

        $reservation->cancel($sessionDate, $now);

        $this->expectException(ReservationAlreadyCancelledException::class);
        $reservation->cancel($sessionDate, $now);
    }
}