<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Doctrine\Type;

use App\Catalog\Domain\Model\ProviderId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class ProviderIdType extends StringType
{
    public function convertToPHPValue($value, AbstractPlatform $platform): ?ProviderId
    {
        return $value === null ? null : new ProviderId($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        return $value instanceof ProviderId ? $value->value() : $value;
    }

    public function getName(): string
    {
        return 'provider_id';
    }
}