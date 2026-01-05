<?php

declare(strict_types=1);

namespace App\DTO;

final class SanitaryControlDto
{
    public function __construct(
        public readonly \DateTimeImmutable $date,
        public readonly ?string $conclusionConformite
    ) {}
}