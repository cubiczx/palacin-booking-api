<?php

declare(strict_types=1);

namespace App\Booking\Infrastructure\Http\Controller;

use App\Booking\Application\ReserveSeats\ReserveSeatsCommand;
use App\Booking\Application\ReserveSeats\ReserveSeatsHandler;
use App\Booking\Infrastructure\Http\Response\ReservationResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ReserveSeatsController
{
    public function __construct(private readonly ReserveSeatsHandler $handler) {}

    #[Route('/api/sessions/{sessionId}/reservations', methods: ['POST'])]
    #[OA\Post(
        summary: 'Reserve your place for a session',
        requestBody: new OA\RequestBody(content: new OA\JsonContent(
            required: ['userId', 'seats', 'contactEmail'],
            properties: [
                new OA\Property(property: 'userId', type: 'string', example: 'user-1', description: 'User identifier'),
                new OA\Property(property: 'seats', type: 'integer', example: 2, description: 'Number of seats to reserve'),
                new OA\Property(property: 'contactEmail', type: 'string', example: 'user@example.com', description: 'Contact email for the reservation'),
            ]
        )),
        responses: [
            new OA\Response(response: 201, description: 'Reservation confirmed', content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'id', type: 'string', description: 'Reservation unique identifier'),
                    new OA\Property(property: 'status', type: 'string', description: 'Current reservation status'),
                ],
                type: 'object'
            )),
            new OA\Response(response: 404, description: 'Session not found'),
            new OA\Response(response: 409, description: 'Cannot reserve (not enough seats or session already started)'),
        ]
    )]
    public function __invoke(string $sessionId, Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true) ?? [];

        $id = ($this->handler)(new ReserveSeatsCommand(
            sessionId: $sessionId,
            userId: $payload['userId'] ?? '',
            seats: (int) ($payload['seats'] ?? 0),
            contactEmail: $payload['contactEmail'] ?? '',
        ));

        return new JsonResponse((new ReservationResponse($id->value(), 'confirmed'))->toArray(), 201);
    }
}