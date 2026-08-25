<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Http\Response;

use OpenApi\Attributes as OA;

#[OA\Schema(
    type: 'object'
)]
final class SessionResponse
{
    #[OA\Property(type: 'string', description: 'Session unique identifier')]
    public readonly string $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function toArray(): array
    {
        return ['id' => $this->id];
    }
}
