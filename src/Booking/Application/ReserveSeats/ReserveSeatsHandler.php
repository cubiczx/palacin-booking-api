<?php

declare(strict_types=1);

namespace App\Booking\Application\ReserveSeats;

use App\Booking\Domain\Exception\NotEnoughSeatsException;
use App\Booking\Domain\Exception\SessionAlreadyStartedException;
use App\Booking\Domain\Exception\SessionNotFoundException;
use App\Booking\Domain\Gateway\SessionAvailabilityGatewayInterface;
use App\Booking\Domain\Model\Reservation;
use App\Booking\Domain\Model\ReservationId;
use App\Booking\Domain\Model\UserId;
use App\Booking\Domain\Notification\ReservationNotifierInterface;
use App\Booking\Domain\Repository\ReservationRepositoryInterface;
use App\Catalog\Domain\Model\SessionId;
use Symfony\Component\Clock\ClockInterface;

final class ReserveSeatsHandler
{
    public function __construct(
        private readonly SessionAvailabilityGatewayInterface $sessionGateway,
        private readonly ReservationRepositoryInterface $reservations,
        private readonly ReservationNotifierInterface $notifier,
        private readonly ClockInterface $clock,
    ) {}

    public function __invoke(ReserveSeatsCommand $command): ReservationId
    {
        $sessionId = SessionId::fromString($command->sessionId);
        $now = $this->clock->now();

        $session = $this->sessionGateway->findSession($sessionId);
        if ($session === null) {
            throw new SessionNotFoundException(sprintf('Session "%s" not found.', $command->sessionId));
        }

        if ($now >= $session->date) {
            throw new SessionAlreadyStartedException('Cannot reserve seats for a session that has already started.');
        }

        // Atomic operation at the BD level: avoids race conditions with
        // many simultaneous reservations competing for the same capacity.
        $reserved = $this->sessionGateway->tryReserveSeats($sessionId, $command->seats);
        if (!$reserved) {
            throw new NotEnoughSeatsException('Not enough seats available for this session.');
        }

        $reservation = Reservation::create(
            ReservationId::generate(),
            $sessionId,
            new UserId($command->userId),
            $command->seats,
            $session->pricePerSeat->multiply($command->seats),
            $command->contactEmail,
            $now,
        );

        $this->reservations->save($reservation);
        $this->notifier->notifyReservationCreated($reservation);

        return $reservation->id();
    }
}