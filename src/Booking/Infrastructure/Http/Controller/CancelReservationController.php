<?php

declare(strict_types=1);

namespace App\Booking\Infrastructure\Http\Controller;

use App\Booking\Application\CancelReservation\CancelReservationCommand;
use App\Booking\Application\CancelReservation\CancelReservationHandler;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class CancelReservationController
{
    public function __construct(private readonly CancelReservationHandler $handler) {}

    #[Route('/api/reservations/{reservationId}/cancel', methods: ['POST'])]
    #[OA\Post(
        summary: 'Cancel a reservation',
        responses: [
            new OA\Response(response: 204, description: 'Reservation cancelled successfully'),
            new OA\Response(response: 404, description: 'Reservation not found'),
            new OA\Response(response: 409, description: 'Cannot cancel reservation (already cancelled or outside cancellation window)'),
        ]
    )]
    public function __invoke(string $reservationId): JsonResponse
    {
        ($this->handler)(new CancelReservationCommand($reservationId));

        return new JsonResponse(null, 204);
    }
}