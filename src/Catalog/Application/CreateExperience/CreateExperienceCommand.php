<?php

declare(strict_types=1);

namespace App\Catalog\Application\CreateExperience;

final class CreateExperienceCommand
{
    public function __construct(
        public readonly string $title,
        public readonly string $description,
        public readonly string $providerId,
    ) {}
}