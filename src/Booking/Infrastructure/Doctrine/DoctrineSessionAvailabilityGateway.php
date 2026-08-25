<?php

declare(strict_types=1);

namespace App\Booking\Infrastructure\Doctrine;

use App\Booking\Domain\Gateway\SessionAvailabilityGatewayInterface;
use App\Booking\Domain\Gateway\SessionSnapshot;
use App\Catalog\Domain\Model\SessionId;
use App\Shared\Domain\ValueObject\Money;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;

final class DoctrineSessionAvailabilityGateway implements SessionAvailabilityGatewayInterface
{
    public function __construct(private readonly Connection $connection) {}

    public function findSession(SessionId $sessionId): ?SessionSnapshot
    {
        $row = $this->connection->fetchAssociative(
            'SELECT id, date, price_cents, price_currency FROM sessions WHERE id = :id',
            ['id' => $sessionId->value()]
        );

        if ($row === false) {
            return null;
        }

        return new SessionSnapshot(
            $sessionId,
            new DateTimeImmutable($row['date']),
            Money::fromCents((int) $row['price_cents'], $row['price_currency']),
        );
    }

    public function tryReserveSeats(SessionId $sessionId, int $seats): bool
    {
        // Compatible with MySQL, PostgreSQL, and SQLite: the WHERE-conditioned
        // UPDATE is row-level atomic in all three engines.
        $affectedRows = $this->connection->executeStatement(
            'UPDATE sessions
            SET available_seats = available_seats - :seats
            WHERE id = :id AND available_seats >= :seats',
            ['seats' => $seats, 'id' => $sessionId->value()],
        );

        return $affectedRows === 1;
    }

    public function releaseSeats(SessionId $sessionId, int $seats): void
    {
        // MIN() is a scalar function supported in MySQL and SQLite (the two database engines
        // used in this project: MySQL in Docker/production, SQLite locally).
        // Note: PostgreSQL does not support MIN(a, b) as a scalar function (only as an
        // aggregate) — if you migrate to Postgres in the future, use LEAST() instead.
        $this->connection->executeStatement(
            'UPDATE sessions
            SET available_seats = MIN(capacity, available_seats + :seats)
            WHERE id = :id',
            ['seats' => $seats, 'id' => $sessionId->value()],
        );
    }
}