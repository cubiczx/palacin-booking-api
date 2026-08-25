<?php

declare(strict_types=1);

namespace App\Tests\Functional\Booking;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CancelReservationControllerTest extends WebTestCase
{
    public function test_canceling_existing_reservation_returns_204(): void
    {
        $client = static::createClient();

        // Create experience
        $client->request('POST', '/api/experiences', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => 'Kayak tour', 'description' => 'desc', 'providerId' => 'provider-1']));
        $experienceId = json_decode($client->getResponse()->getContent(), true)['id'];

        // Create session
        $client->request('POST', "/api/experiences/{$experienceId}/sessions", [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['date' => '2030-01-01T10:00:00', 'capacity' => 5, 'priceInCents' => 1000]));
        $sessionId = json_decode($client->getResponse()->getContent(), true)['id'];

        // Create reservation
        $client->request('POST', "/api/sessions/{$sessionId}/reservations", [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['userId' => 'user-1', 'seats' => 2, 'contactEmail' => 'a@example.com']));
        $response = json_decode($client->getResponse()->getContent(), true);
        $reservationId = $response['id'];

        // Cancel reservation
        $client->request('POST', "/api/reservations/{$reservationId}/cancel", [], [], ['CONTENT_TYPE' => 'application/json']);

        self::assertSame(204, $client->getResponse()->getStatusCode());
    }

    public function test_seats_are_released_after_cancellation(): void
    {
        $client = static::createClient();

        // Create experience
        $client->request('POST', '/api/experiences', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => 'Kayak tour', 'description' => 'desc', 'providerId' => 'provider-1']));
        $experienceId = json_decode($client->getResponse()->getContent(), true)['id'];

        // Create session with limited capacity
        $client->request('POST', "/api/experiences/{$experienceId}/sessions", [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['date' => '2030-01-01T10:00:00', 'capacity' => 2, 'priceInCents' => 1000]));
        $sessionId = json_decode($client->getResponse()->getContent(), true)['id'];

        // Create first reservation
        $client->request('POST', "/api/sessions/{$sessionId}/reservations", [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['userId' => 'user-1', 'seats' => 2, 'contactEmail' => 'a@example.com']));
        $firstReservation = json_decode($client->getResponse()->getContent(), true);
        $reservationId = $firstReservation['id'];

        // Try to create second reservation (should fail - no seats available)
        $client->request('POST', "/api/sessions/{$sessionId}/reservations", [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['userId' => 'user-2', 'seats' => 1, 'contactEmail' => 'b@example.com']));
        self::assertSame(409, $client->getResponse()->getStatusCode());

        // Cancel first reservation
        $client->request('POST', "/api/reservations/{$reservationId}/cancel", [], [], ['CONTENT_TYPE' => 'application/json']);
        self::assertSame(204, $client->getResponse()->getStatusCode());

        // Now second reservation should succeed
        $client->request('POST', "/api/sessions/{$sessionId}/reservations", [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['userId' => 'user-2', 'seats' => 1, 'contactEmail' => 'b@example.com']));
        self::assertSame(201, $client->getResponse()->getStatusCode());
    }

    public function test_canceling_multiple_different_reservations(): void
    {
        $client = static::createClient();

        // Create experience
        $client->request('POST', '/api/experiences', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => 'Kayak tour', 'description' => 'desc', 'providerId' => 'provider-1']));
        $experienceId = json_decode($client->getResponse()->getContent(), true)['id'];

        // Create session
        $client->request('POST', "/api/experiences/{$experienceId}/sessions", [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['date' => '2030-01-01T10:00:00', 'capacity' => 10, 'priceInCents' => 1000]));
        $sessionId = json_decode($client->getResponse()->getContent(), true)['id'];

        // Create two reservations
        $client->request('POST', "/api/sessions/{$sessionId}/reservations", [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['userId' => 'user-1', 'seats' => 2, 'contactEmail' => 'a@example.com']));
        $firstReservation = json_decode($client->getResponse()->getContent(), true)['id'];

        $client->request('POST', "/api/sessions/{$sessionId}/reservations", [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['userId' => 'user-2', 'seats' => 3, 'contactEmail' => 'b@example.com']));
        $secondReservation = json_decode($client->getResponse()->getContent(), true)['id'];

        // Cancel first reservation
        $client->request('POST', "/api/reservations/{$firstReservation}/cancel", [], [], ['CONTENT_TYPE' => 'application/json']);
        self::assertSame(204, $client->getResponse()->getStatusCode());

        // Cancel second reservation
        $client->request('POST', "/api/reservations/{$secondReservation}/cancel", [], [], ['CONTENT_TYPE' => 'application/json']);
        self::assertSame(204, $client->getResponse()->getStatusCode());
    }

    public function test_canceling_non_existent_reservation_returns_404(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/reservations/00000000-0000-0000-0000-000000000000/cancel', [], [], ['CONTENT_TYPE' => 'application/json']);

        self::assertSame(404, $client->getResponse()->getStatusCode());
    }

    public function test_canceling_already_cancelled_reservation_returns_409(): void
    {
        $client = static::createClient();

        // Create experience
        $client->request('POST', '/api/experiences', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => 'Kayak tour', 'description' => 'desc', 'providerId' => 'provider-1']));
        $experienceId = json_decode($client->getResponse()->getContent(), true)['id'];

        // Create session
        $client->request('POST', "/api/experiences/{$experienceId}/sessions", [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['date' => '2030-01-01T10:00:00', 'capacity' => 5, 'priceInCents' => 1000]));
        $sessionId = json_decode($client->getResponse()->getContent(), true)['id'];

        // Create reservation
        $client->request('POST', "/api/sessions/{$sessionId}/reservations", [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['userId' => 'user-1', 'seats' => 2, 'contactEmail' => 'a@example.com']));
        $reservationId = json_decode($client->getResponse()->getContent(), true)['id'];

        // Cancel it once
        $client->request('POST', "/api/reservations/{$reservationId}/cancel", [], [], ['CONTENT_TYPE' => 'application/json']);
        self::assertSame(204, $client->getResponse()->getStatusCode());

        // Try to cancel again
        $client->request('POST', "/api/reservations/{$reservationId}/cancel", [], [], ['CONTENT_TYPE' => 'application/json']);
        self::assertSame(409, $client->getResponse()->getStatusCode());
    }
}
