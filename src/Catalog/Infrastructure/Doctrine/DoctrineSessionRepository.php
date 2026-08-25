<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Doctrine;

use App\Catalog\Domain\Model\ExperienceId;
use App\Catalog\Domain\Model\Session;
use App\Catalog\Domain\Repository\SessionRepositoryInterface;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineSessionRepository implements SessionRepositoryInterface
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    public function save(Session $session): void
    {
        $this->em->persist($session);
        $this->em->flush();
    }

    public function existsForExperienceOnDate(ExperienceId $experienceId, DateTimeImmutable $date): bool
    {
        $count = $this->em->createQueryBuilder()
            ->select('COUNT(s.id)')
            ->from(Session::class, 's')
            ->where('s.experienceId = :experienceId')
            ->andWhere('s.date BETWEEN :start AND :end')
            ->setParameter('experienceId', $experienceId->value())
            ->setParameter('start', $date->setTime(0, 0, 0))
            ->setParameter('end', $date->setTime(23, 59, 59))
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $count > 0;
    }
}