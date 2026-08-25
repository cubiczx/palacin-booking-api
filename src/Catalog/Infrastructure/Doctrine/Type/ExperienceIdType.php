<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Doctrine\Type;

use App\Catalog\Domain\Model\ExperienceId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class ExperienceIdType extends StringType
{
    public function convertToPHPValue($value, AbstractPlatform $platform): ?ExperienceId
    {
        return $value === null ? null : ExperienceId::fromString($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        return $value instanceof ExperienceId ? $value->value() : $value;
    }

    public function getName(): string
    {
        return 'experience_id';
    }
}