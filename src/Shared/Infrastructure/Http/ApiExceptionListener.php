<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Http;

use App\Shared\Domain\Exception\ApiProblemExceptionInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[AsEventListener(event: 'kernel.exception')]
final class ApiExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof ApiProblemExceptionInterface) {
            $event->setResponse(new JsonResponse(
                [
                    'type' => $exception->errorType(),
                    'title' => $exception->getMessage(),
                    'status' => $exception->statusCode(),
                ],
                $exception->statusCode(),
                ['Content-Type' => 'application/problem+json']
            ));

            return;
        }

        if ($exception instanceof ValidationFailedException) {
            $errors = [];
            foreach ($exception->getViolations() as $violation) {
                $errors[] = [
                    'field' => $violation->getPropertyPath(),
                    'message' => $violation->getMessage(),
                ];
            }

            $event->setResponse(new JsonResponse(
                ['type' => 'validation_error', 'title' => 'Invalid request', 'status' => 422, 'errors' => $errors],
                422,
                ['Content-Type' => 'application/problem+json']
            ));
        }
    }
}