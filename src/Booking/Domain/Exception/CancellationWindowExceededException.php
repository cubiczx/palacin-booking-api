<?php

declare(strict_types=1);

namespace App\Booking\Domain\Exception;

use App\Shared\Domain\Exception\ApiProblemExceptionInterface;

final class CancellationWindowExceededException extends \DomainException implements ApiProblemExceptionInterface
{
    public function statusCode(): int { return 409; }
    public function errorType(): string { return 'cancellation_window_exceeded'; }
}