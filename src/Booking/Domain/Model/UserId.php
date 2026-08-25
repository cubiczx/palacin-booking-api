<?php

declare(strict_types=1);

namespace App\Booking\Domain\Model;

final class UserId
{
    public function __construct(private readonly string $value)
    {
        if (trim($value) === '') {
            throw new \InvalidArgumentException('UserId cannot be empty.');
        }
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