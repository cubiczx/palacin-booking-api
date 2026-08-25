<?php

declare(strict_types=1);

namespace App\Catalog\Application\CreateSession;

final class CreateSessionCommand
{
    public function __construct(
        public readonly string $experienceId,
        public readonly string $date, // ISO 8601
        public readonly int $capacity,
        public readonly int $priceInCents,
    ) {}
}