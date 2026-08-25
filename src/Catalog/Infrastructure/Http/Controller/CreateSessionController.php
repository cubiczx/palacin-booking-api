<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Http\Controller;

use App\Catalog\Application\CreateSession\CreateSessionCommand;
use App\Catalog\Application\CreateSession\CreateSessionHandler;
use App\Catalog\Infrastructure\Http\Response\SessionResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class CreateSessionController
{
    public function __construct(private readonly CreateSessionHandler $handler) {}

    #[Route('/api/experiences/{experienceId}/sessions', methods: ['POST'])]
    #[OA\Post(
        summary: 'Create a session for an experience',
        requestBody: new OA\RequestBody(content: new OA\JsonContent(
            required: ['date', 'capacity', 'priceInCents'],
            properties: [
                new OA\Property(property: 'date', type: 'string', example: '2030-01-01T10:00:00', description: 'Session date in ISO 8601 format'),
                new OA\Property(property: 'capacity', type: 'integer', example: 5, description: 'Number of available seats'),
                new OA\Property(property: 'priceInCents', type: 'integer', example: 1000, description: 'Price per seat in cents'),
            ]
        )),
        responses: [
            new OA\Response(response: 201, description: 'Session created', content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'id', type: 'string', description: 'Session unique identifier'),
                    new OA\Property(property: 'availableSeats', type: 'integer', description: 'Number of available seats'),
                ],
                type: 'object'
            )),
            new OA\Response(response: 404, description: 'Experience not found'),
            new OA\Response(response: 409, description: 'Duplicate session for the same date'),
            new OA\Response(response: 422, description: 'Invalid capacity or session date in the past'),
        ]
    )]
    public function __invoke(string $experienceId, Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true) ?? [];

        $id = ($this->handler)(new CreateSessionCommand(
            experienceId: $experienceId,
            date: $payload['date'] ?? '',
            capacity: (int) ($payload['capacity'] ?? 0),
            priceInCents: (int) ($payload['priceInCents'] ?? 0),
        ));

        return new JsonResponse((new SessionResponse($id->value()))->toArray(), 201);
    }
}