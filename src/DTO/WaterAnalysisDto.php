<?php

declare(strict_types=1);

namespace App\DTO;

final class WaterAnalysisDto
{
    public function __construct(
        public readonly string $parameter,
        public readonly ?float $value,
        public readonly ?string $unit,
        public readonly \DateTimeImmutable $date
    ){}
}