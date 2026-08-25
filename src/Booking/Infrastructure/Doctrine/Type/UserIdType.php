<?php

declare(strict_types=1);

namespace App\Booking\Infrastructure\Doctrine\Type;

use App\Booking\Domain\Model\UserId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class UserIdType extends StringType
{
    public function convertToPHPValue($value, AbstractPlatform $platform): ?UserId
    {
        return $value === null ? null : new UserId($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        return $value instanceof UserId ? $value->value() : $value;
    }

    public function getName(): string
    {
        return 'user_id';
    }
}