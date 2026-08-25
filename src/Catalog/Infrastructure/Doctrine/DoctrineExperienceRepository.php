<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Doctrine;

use App\Catalog\Domain\Model\Experience;
use App\Catalog\Domain\Model\ExperienceId;
use App\Catalog\Domain\Repository\ExperienceRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineExperienceRepository implements ExperienceRepositoryInterface
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    public function save(Experience $experience): void
    {
        $this->em->persist($experience);
        $this->em->flush();
    }

    public function ofId(ExperienceId $id): ?Experience
    {
        return $this->em->find(Experience::class, $id->value());
    }
}