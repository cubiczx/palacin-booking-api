<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Repository;

use App\Catalog\Domain\Model\Experience;
use App\Catalog\Domain\Model\ExperienceId;

interface ExperienceRepositoryInterface
{
    public function save(Experience $experience): void;

    public function ofId(ExperienceId $id): ?Experience;
}