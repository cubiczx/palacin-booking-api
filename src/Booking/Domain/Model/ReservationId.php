<?php

declare(strict_types=1);

namespace App\Booking\Domain\Model;

use Symfony\Component\Uid\Uuid;

final class ReservationId
{
    public function __construct(private readonly string $value)
    {
        if (trim($value) === '') {
            throw new \InvalidArgumentException('ReservationId cannot be empty.');
        }
    }

    public static function generate(): self
    {
        return new self(Uuid::v7()->toRfc4122());
    }

    public static function fromString(string $value): self
    {
        if (!Uuid::isValid($value)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid ReservationId.', $value));
        }

        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}