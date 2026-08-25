<?php

declare(strict_types=1);

namespace App\Booking\Infrastructure\Notification;

use App\Booking\Domain\Model\Reservation;
use App\Booking\Domain\Notification\ReservationNotifierInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * Actual shipping adapter (transport set to null:// in .env, as
 * requested in the statement.
 */
final class MailerReservationNotifier implements ReservationNotifierInterface
{
    public function __construct(private readonly MailerInterface $mailer) {}

    public function notifyReservationCreated(Reservation $reservation): void
    {
        $this->send(
            $reservation->contactEmail(),
            'Reservation confirmed',
            sprintf('Your reservation %s for %d seat(s) has been confirmed.', $reservation->id()->value(), $reservation->seats()),
        );
    }

    public function notifyReservationCancelled(Reservation $reservation): void
    {
        $this->send(
            $reservation->contactEmail(),
            'Reservation cancelled',
            sprintf('Your reservation %s has been cancelled.', $reservation->id()->value()),
        );
    }

    private function send(string $to, string $subject, string $body): void
    {
        $email = (new Email())
            ->from('no-reply@palacin-booking.example')
            ->to($to)
            ->subject($subject)
            ->text($body);

        $this->mailer->send($email);
    }
}