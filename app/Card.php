<?php

declare(strict_types=1);

namespace App;

use App\Enums\Color;
use App\Enums\Number;

final class Card
{
    public bool $knownColor = false;
    public bool $knownNumber = false;

    public function __construct(public Color $color, public Number $number) {}

    public function render(bool $hidden = false): string
    {
        return view('card', [
            'card' => $this,
            'hidden' => $hidden
        ])->render();
    }
}
