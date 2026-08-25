<?php

declare(strict_types=1);

namespace App\Tests\Functional\Catalog;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CreateSessionControllerTest extends WebTestCase
{
    public function test_creating_session_returns_201_with_id(): void
    {
        $client = static::createClient();

        // First create an experience
        $client->request('POST', '/api/experiences', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => 'Kayak tour', 'description' => 'desc', 'providerId' => 'provider-1']));
        $experienceId = json_decode($client->getResponse()->getContent(), true)['id'];

        // Create a session
        $client->request('POST', "/api/experiences/{$experienceId}/sessions", [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['date' => '2030-01-01T10:00:00', 'capacity' => 5, 'priceInCents' => 1000]));

        self::assertSame(201, $client->getResponse()->getStatusCode());
        
        $response = json_decode($client->getResponse()->getContent(), true);
        self::assertIsArray($response);
        self::assertArrayHasKey('id', $response);
        self::assertIsString($response['id']);
    }

    public function test_creating_multiple_sessions_for_same_experience(): void
    {
        $client = static::createClient();

        // Create an experience
        $client->request('POST', '/api/experiences', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => 'Kayak tour', 'description' => 'desc', 'providerId' => 'provider-1']));
        $experienceId = json_decode($client->getResponse()->getContent(), true)['id'];

        // Create first session
        $client->request('POST', "/api/experiences/{$experienceId}/sessions", [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['date' => '2030-01-01T10:00:00', 'capacity' => 5, 'priceInCents' => 1000]));
        self::assertSame(201, $client->getResponse()->getStatusCode());
        $firstSessionId = json_decode($client->getResponse()->getContent(), true)['id'];

        // Create second session for same experience, different date
        $client->request('POST', "/api/experiences/{$experienceId}/sessions", [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['date' => '2030-01-02T10:00:00', 'capacity' => 3, 'priceInCents' => 1500]));
        self::assertSame(201, $client->getResponse()->getStatusCode());
        $secondSessionId = json_decode($client->getResponse()->getContent(), true)['id'];

        self::assertNotEquals($firstSessionId, $secondSessionId);
    }

    public function test_creating_sessions_with_different_capacities(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/experiences', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => 'Kayak tour', 'description' => 'desc', 'providerId' => 'provider-1']));
        $experienceId = json_decode($client->getResponse()->getContent(), true)['id'];

        // Session with high capacity
        $client->request('POST', "/api/experiences/{$experienceId}/sessions", [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['date' => '2030-01-01T10:00:00', 'capacity' => 100, 'priceInCents' => 1000]));
        self::assertSame(201, $client->getResponse()->getStatusCode());

        // Session with low capacity
        $client->request('POST', "/api/experiences/{$experienceId}/sessions", [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['date' => '2030-02-01T10:00:00', 'capacity' => 1, 'priceInCents' => 2000]));
        self::assertSame(201, $client->getResponse()->getStatusCode());
    }

    public function test_creating_session_for_non_existent_experience_returns_404(): void
    {
        $client = static::createClient();

        // Try to create session for experience that doesn't exist
        $client->request('POST', '/api/experiences/00000000-0000-0000-0000-000000000000/sessions', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['date' => '2030-01-01T10:00:00', 'capacity' => 5, 'priceInCents' => 1000]));

        self::assertSame(404, $client->getResponse()->getStatusCode());
    }

    public function test_creating_duplicate_session_on_same_date_returns_409(): void
    {
        $client = static::createClient();

        // Create experience
        $client->request('POST', '/api/experiences', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => 'Kayak tour', 'description' => 'desc', 'providerId' => 'provider-1']));
        $experienceId = json_decode($client->getResponse()->getContent(), true)['id'];

        // Create first session
        $client->request('POST', "/api/experiences/{$experienceId}/sessions", [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['date' => '2030-01-01T10:00:00', 'capacity' => 5, 'priceInCents' => 1000]));
        self::assertSame(201, $client->getResponse()->getStatusCode());

        // Try to create another session for same date (different time but same day)
        $client->request('POST', "/api/experiences/{$experienceId}/sessions", [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['date' => '2030-01-01T14:00:00', 'capacity' => 5, 'priceInCents' => 1500]));

        self::assertSame(409, $client->getResponse()->getStatusCode());
    }
}
