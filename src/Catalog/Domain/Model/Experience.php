<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Model;

final class Experience
{
    private function __construct(
        private readonly ExperienceId $id,
        private string $title,
        private string $description,
        private readonly ProviderId $providerId,
    ) {}

    public static function create(
        ExperienceId $id,
        string $title,
        string $description,
        ProviderId $providerId,
    ): self {
        if (trim($title) === '') {
            throw new \InvalidArgumentException('Experience title cannot be empty.');
        }

        return new self($id, $title, $description, $providerId);
    }

    public function id(): ExperienceId
    {
        return $this->id;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function description(): string
    {
        return $this->description;
    }


    public function providerId(): ProviderId
    {
        return $this->providerId;
    }
}