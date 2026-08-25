<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception;

use App\Shared\Domain\Exception\ApiProblemExceptionInterface;

final class DuplicateSessionDateException extends \DomainException implements ApiProblemExceptionInterface
{
    public function statusCode(): int { return 409; }
    public function errorType(): string { return 'duplicate_session_date'; }
}