<?php

namespace App\ValueObjects;

use App\Enums\HintType;

final readonly class Hint
{
    public function __construct(public HintType $type, public string $value) { }
}
