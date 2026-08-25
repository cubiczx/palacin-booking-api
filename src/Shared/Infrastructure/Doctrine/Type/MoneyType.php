<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Doctrine\Type;

use App\Shared\Domain\ValueObject\Money;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class MoneyType extends Type
{
    public const NAME = 'money_type';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'JSON';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Money
    {
        if ($value === null) {
            return null;
        }

        $data = json_decode($value, true);
        if (!is_array($data) || !isset($data['amountInCents'], $data['currency'])) {
            throw new \InvalidArgumentException('Invalid Money JSON format');
        }

        return Money::fromCents($data['amountInCents'], $data['currency']);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof Money) {
            throw new \InvalidArgumentException('Expected Money instance');
        }

        return json_encode([
            'amountInCents' => $value->amountInCents(),
            'currency' => $value->currency(),
        ]);
    }

    public function getName(): string
    {
        return self::NAME;
    }
}

