<?php

declare(strict_types=1);

namespace App\Catalog\Application\CreateExperience;

use App\Catalog\Domain\Model\Experience;
use App\Catalog\Domain\Model\ExperienceId;
use App\Catalog\Domain\Model\ProviderId;
use App\Catalog\Domain\Repository\ExperienceRepositoryInterface;

final class CreateExperienceHandler
{
    public function __construct(private readonly ExperienceRepositoryInterface $experiences) {}

    public function __invoke(CreateExperienceCommand $command): ExperienceId
    {
        $experience = Experience::create(
            ExperienceId::generate(),
            $command->title,
            $command->description,
            new ProviderId($command->providerId),
        );

        $this->experiences->save($experience);

        return $experience->id();
    }
}