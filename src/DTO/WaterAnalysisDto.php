<?php

declare(strict_types=1);

namespace App\DTO;

use App\Enums\WaterParameter;

final class WaterAnalysisDto
{
    public function __construct(
        public readonly WaterParameter $parameter,
        public readonly ?float $value,
        public readonly ?string $unit,
        public readonly \DateTimeImmutable $date
    ){}
}