<?php

declare(strict_types=1);

namespace App\Catalog\Application\CreateSession;

use App\Catalog\Domain\Exception\DuplicateSessionDateException;
use App\Catalog\Domain\Exception\ExperienceNotFoundException;
use App\Catalog\Domain\Model\ExperienceId;
use App\Catalog\Domain\Model\Session;
use App\Catalog\Domain\Model\SessionId;
use App\Catalog\Domain\Repository\ExperienceRepositoryInterface;
use App\Catalog\Domain\Repository\SessionRepositoryInterface;
use App\Shared\Domain\ValueObject\Money;
use DateTimeImmutable;
use Symfony\Component\Clock\ClockInterface;

final class CreateSessionHandler
{
    public function __construct(
        private readonly ExperienceRepositoryInterface $experiences,
        private readonly SessionRepositoryInterface $sessions,
        private readonly ClockInterface $clock,
    ) {}

    public function __invoke(CreateSessionCommand $command): SessionId
    {
        $experienceId = ExperienceId::fromString($command->experienceId);

        if ($this->experiences->ofId($experienceId) === null) {
            throw new ExperienceNotFoundException(sprintf('Experience "%s" not found.', $command->experienceId));
        }

        $date = new DateTimeImmutable($command->date);

        if ($this->sessions->existsForExperienceOnDate($experienceId, $date)) {
            throw new DuplicateSessionDateException('An experience cannot have two sessions the same day.');
        }

        $session = Session::create(
            SessionId::generate(),
            $experienceId,
            $date,
            $command->capacity,
            Money::fromCents($command->priceInCents),
            $this->clock->now(),
        );

        $this->sessions->save($session);

        return $session->id();
    }
}