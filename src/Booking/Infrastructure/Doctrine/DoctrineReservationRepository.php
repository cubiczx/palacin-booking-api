<?php

declare(strict_types=1);

namespace App\Booking\Infrastructure\Doctrine;

use App\Booking\Domain\Model\Reservation;
use App\Booking\Domain\Model\ReservationId;
use App\Booking\Domain\Repository\ReservationRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineReservationRepository implements ReservationRepositoryInterface
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    public function save(Reservation $reservation): void
    {
        $this->em->persist($reservation);
        $this->em->flush();
    }

    public function ofId(ReservationId $id): ?Reservation
    {
        return $this->em->find(Reservation::class, $id->value());
    }
}