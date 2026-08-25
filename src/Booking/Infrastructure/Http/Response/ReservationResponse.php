<?php

declare(strict_types=1);

namespace App\Booking\Infrastructure\Http\Response;

use OpenApi\Attributes as OA;

#[OA\Schema(
    type: 'object'
)]
final class ReservationResponse
{
    #[OA\Property(type: 'string', description: 'Reservation unique identifier')]
    public readonly string $id;

    #[OA\Property(type: 'string', description: 'Reservation status')]
    public readonly string $status;

    public function __construct(
        string $id,
        string $status = 'confirmed',
    ) {
        $this->id = $id;
        $this->status = $status;
    }

    public function toArray(): array
    {
        return ['id' => $this->id, 'status' => $this->status];
    }
}
