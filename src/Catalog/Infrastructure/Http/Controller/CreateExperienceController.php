<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Http\Controller;

use App\Catalog\Application\CreateExperience\CreateExperienceCommand;
use App\Catalog\Application\CreateExperience\CreateExperienceHandler;
use App\Catalog\Infrastructure\Http\Response\ExperienceResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class CreateExperienceController
{
    public function __construct(private readonly CreateExperienceHandler $handler) {}

    #[Route('/api/experiences', methods: ['POST'])]
    #[OA\Post(
        summary: 'Record a new experience',
        requestBody: new OA\RequestBody(content: new OA\JsonContent(
            required: ['title', 'description', 'providerId'],
            properties: [
                new OA\Property(property: 'title', type: 'string', example: 'Kayak tour', description: 'Experience title'),
                new OA\Property(property: 'description', type: 'string', example: 'An amazing kayak tour', description: 'Experience description'),
                new OA\Property(property: 'providerId', type: 'string', example: 'provider-1', description: 'Provider identifier'),
            ]
        )),
        responses: [
            new OA\Response(response: 201, description: 'Experience created', content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'id', type: 'string', description: 'Experience unique identifier'),
                ],
                type: 'object'
            )),
            new OA\Response(response: 422, description: 'Invalid input (empty title or providerId)'),
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true) ?? [];

        $id = ($this->handler)(new CreateExperienceCommand(
            title: $payload['title'] ?? '',
            description: $payload['description'] ?? '',
            providerId: $payload['providerId'] ?? '',
        ));

        return new JsonResponse((new ExperienceResponse($id->value()))->toArray(), 201);
    }
}