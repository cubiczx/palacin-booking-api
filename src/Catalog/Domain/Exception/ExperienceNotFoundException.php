<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception;

use App\Shared\Domain\Exception\ApiProblemExceptionInterface;

final class ExperienceNotFoundException extends \DomainException implements ApiProblemExceptionInterface
{
    public function statusCode(): int { return 404; }
    public function errorType(): string { return 'experience_not_found'; }
}