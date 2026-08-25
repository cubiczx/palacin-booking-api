<?php

declare(strict_types=1);

namespace App\Booking\Domain\Exception;

use App\Shared\Domain\Exception\ApiProblemExceptionInterface;

final class SessionNotFoundException extends \DomainException implements ApiProblemExceptionInterface
{
    public function statusCode(): int { return 404; }
    public function errorType(): string { return 'session_not_found'; }
}