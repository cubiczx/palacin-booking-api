<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception;

interface ApiProblemExceptionInterface
{
    public function statusCode(): int;

    public function errorType(): string;
}