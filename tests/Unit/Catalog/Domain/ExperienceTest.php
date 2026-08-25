<?php

declare(strict_types=1);

namespace App\Tests\Unit\Catalog\Domain;

use App\Catalog\Domain\Model\Experience;
use App\Catalog\Domain\Model\ExperienceId;
use App\Catalog\Domain\Model\ProviderId;
use PHPUnit\Framework\TestCase;

final class ExperienceTest extends TestCase
{
    public function test_creating_experience_with_valid_data(): void
    {
        $id = ExperienceId::fromString('123e4567-e89b-12d3-a456-426614174000');
        $title = 'Kayak tour';
        $description = 'An amazing kayak tour';
        $providerId = new ProviderId('provider-1');

        $experience = Experience::create($id, $title, $description, $providerId);

        self::assertSame('Kayak tour', $experience->title());
        self::assertSame('An amazing kayak tour', $experience->description());
        self::assertTrue($id->equals($experience->id()));
        self::assertSame('provider-1', $experience->providerId()->value());
    }

    public function test_creating_experience_with_empty_title_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Experience title cannot be empty./');

        $id = ExperienceId::fromString('123e4567-e89b-12d3-a456-426614174000');
        $providerId = new ProviderId('provider-1');

        Experience::create($id, '', 'description', $providerId);
    }

    public function test_creating_experience_with_whitespace_title_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Experience title cannot be empty./');

        $id = ExperienceId::fromString('123e4567-e89b-12d3-a456-426614174000');
        $providerId = new ProviderId('provider-1');

        Experience::create($id, '   ', 'description', $providerId);
    }
}
