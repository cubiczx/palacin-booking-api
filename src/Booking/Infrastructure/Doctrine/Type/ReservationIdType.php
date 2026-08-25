<?php

declare(strict_types=1);

namespace App\Booking\Infrastructure\Doctrine\Type;

use App\Booking\Domain\Model\ReservationId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class ReservationIdType extends StringType
{
    public function convertToPHPValue($value, AbstractPlatform $platform): ?ReservationId
    {
        return $value === null ? null : ReservationId::fromString($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        return $value instanceof ReservationId ? $value->value() : $value;
    }

    public function getName(): string
    {
        return 'reservation_id';
    }
}