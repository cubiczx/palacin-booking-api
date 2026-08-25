<?php

declare(strict_types=1);

namespace App\Booking\Application\CancelReservation;

use App\Booking\Domain\Exception\ReservationNotFoundException;
use App\Booking\Domain\Exception\SessionNotFoundException;
use App\Booking\Domain\Gateway\SessionAvailabilityGatewayInterface;
use App\Booking\Domain\Model\ReservationId;
use App\Booking\Domain\Notification\ReservationNotifierInterface;
use App\Booking\Domain\Repository\ReservationRepositoryInterface;
use Symfony\Component\Clock\ClockInterface;

final class CancelReservationHandler
{
    public function __construct(
        private readonly ReservationRepositoryInterface $reservations,
        private readonly SessionAvailabilityGatewayInterface $sessionGateway,
        private readonly ReservationNotifierInterface $notifier,
        private readonly ClockInterface $clock,
    ) {}

    public function __invoke(CancelReservationCommand $command): void
    {
        $reservation = $this->reservations->ofId(ReservationId::fromString($command->reservationId));
        if ($reservation === null) {
            throw new ReservationNotFoundException('Reservation not found.');
        }

        $session = $this->sessionGateway->findSession($reservation->sessionId());
        if ($session === null) {
            throw new SessionNotFoundException('Associated session not found.');
        }

        $reservation->cancel($session->date, $this->clock->now());

        $this->reservations->save($reservation);
        $this->sessionGateway->releaseSeats($reservation->sessionId(), $reservation->seats());
        $this->notifier->notifyReservationCancelled($reservation);
    }
}