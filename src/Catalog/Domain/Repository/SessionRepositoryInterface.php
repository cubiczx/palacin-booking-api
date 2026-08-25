<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Repository;

use App\Catalog\Domain\Model\ExperienceId;
use App\Catalog\Domain\Model\Session;
use DateTimeImmutable;

interface SessionRepositoryInterface
{
    public function save(Session $session): void;

    public function existsForExperienceOnDate(ExperienceId $experienceId, DateTimeImmutable $date): bool;
}