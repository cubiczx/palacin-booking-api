<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Doctrine\Type;

use App\Catalog\Domain\Model\SessionId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class SessionIdType extends StringType
{
    public function convertToPHPValue($value, AbstractPlatform $platform): ?SessionId
    {
        return $value === null ? null : SessionId::fromString($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        return $value instanceof SessionId ? $value->value() : $value;
    }

    public function getName(): string
    {
        return 'session_id';
    }
}