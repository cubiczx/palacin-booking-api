<?php

declare(strict_types=1);

namespace App\Tests\Functional\Catalog;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CreateExperienceControllerTest extends WebTestCase
{
    public function test_creating_experience_returns_201_with_id(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/experiences', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => 'Kayak tour', 'description' => 'An amazing kayak tour', 'providerId' => 'provider-1']));

        self::assertSame(201, $client->getResponse()->getStatusCode());
        
        $response = json_decode($client->getResponse()->getContent(), true);
        self::assertIsArray($response);
        self::assertArrayHasKey('id', $response);
        self::assertIsString($response['id']);
    }

    public function test_creating_multiple_experiences(): void
    {
        $client = static::createClient();

        // First experience
        $client->request('POST', '/api/experiences', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => 'Kayak tour', 'description' => 'Water sport', 'providerId' => 'provider-1']));
        self::assertSame(201, $client->getResponse()->getStatusCode());
        $firstId = json_decode($client->getResponse()->getContent(), true)['id'];

        // Second experience
        $client->request('POST', '/api/experiences', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => 'Hiking tour', 'description' => 'Mountain adventure', 'providerId' => 'provider-2']));
        self::assertSame(201, $client->getResponse()->getStatusCode());
        $secondId = json_decode($client->getResponse()->getContent(), true)['id'];

        self::assertNotEquals($firstId, $secondId);
    }
}

