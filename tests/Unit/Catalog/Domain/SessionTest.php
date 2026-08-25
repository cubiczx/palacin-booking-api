<?php

declare(strict_types=1);

namespace App\Tests\Unit\Catalog\Domain;

use App\Catalog\Domain\Exception\PastSessionDateException;
use App\Catalog\Domain\Model\ExperienceId;
use App\Catalog\Domain\Model\Session;
use App\Catalog\Domain\Model\SessionId;
use App\Shared\Domain\ValueObject\Money;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use App\Catalog\Domain\Exception\InvalidCapacityException;

final class SessionTest extends TestCase
{
    public function test_cannot_create_session_in_the_past(): void
    {
        $now = new DateTimeImmutable('2026-08-25 10:00:00');

        $this->expectException(PastSessionDateException::class);

        Session::create(
            SessionId::generate(), ExperienceId::generate(),
            new DateTimeImmutable('2026-08-20 10:00:00'), 10, Money::fromCents(1000), $now,
        );
    }

    public function test_cannot_create_session_with_zero_capacity(): void
    {
        $now = new DateTimeImmutable('2026-08-25 10:00:00');

        $this->expectException(InvalidCapacityException::class);
        $this->expectExceptionMessageMatches('/Session capacity must be greater than zero./');

        Session::create(
            SessionId::generate(), ExperienceId::generate(),
            new DateTimeImmutable('2026-08-30 10:00:00'), 0, Money::fromCents(1000), $now,
        );
    }

    public function test_cannot_create_session_with_negative_capacity(): void
    {
        $now = new DateTimeImmutable('2026-08-25 10:00:00');

        $this->expectException(InvalidCapacityException::class);
        $this->expectExceptionMessageMatches('/Session capacity must be greater than zero./');

        Session::create(
            SessionId::generate(), ExperienceId::generate(),
            new DateTimeImmutable('2026-08-30 10:00:00'), -5, Money::fromCents(1000), $now,
        );
    }

    public function test_cannot_reserve_more_seats_than_available(): void
    {
        $now = new DateTimeImmutable('2026-08-25 10:00:00');
        $session = Session::create(
            SessionId::generate(), ExperienceId::generate(),
            new DateTimeImmutable('2026-08-30 10:00:00'), 5, Money::fromCents(1000), $now,
        );

        $session->reserve(5);

        $this->expectException(\DomainException::class);
        $session->reserve(1);
    }
}