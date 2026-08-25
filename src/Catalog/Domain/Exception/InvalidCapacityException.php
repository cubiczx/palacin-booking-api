<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception;

use App\Shared\Domain\Exception\ApiProblemExceptionInterface;

final class InvalidCapacityException extends \DomainException implements ApiProblemExceptionInterface
{
    public function statusCode(): int { return 422; }
    public function errorType(): string { return 'invalid_capacity'; }
}