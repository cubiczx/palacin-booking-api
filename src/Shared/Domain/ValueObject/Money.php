<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use InvalidArgumentException;

final class Money
{
    private function __construct(
        private readonly int $amountInCents,
        private readonly string $currency = 'EUR',
    ) {
        if ($amountInCents < 0) {
            throw new InvalidArgumentException('Money amount cannot be negative.');
        }
    }

    public static function fromCents(int $amountInCents, string $currency = 'EUR'): self
    {
        return new self($amountInCents, $currency);
    }

    public function multiply(int $factor): self
    {
        return new self($this->amountInCents * $factor, $this->currency);
    }

    public function amountInCents(): int
    {
        return $this->amountInCents;
    }

    public function currency(): string
    {
        return $this->currency;
    }
}