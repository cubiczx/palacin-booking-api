<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Model;

final class ProviderId
{
    public function __construct(private readonly string $value)
    {
        if (trim($value) === '') {
            throw new \InvalidArgumentException('ProviderId cannot be empty.');
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