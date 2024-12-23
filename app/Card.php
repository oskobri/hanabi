<?php

declare(strict_types=1);

namespace App;

use App\Enums\Color;
use App\Enums\Number;

final readonly class Card
{
    public function __construct(public Color $color, public Number $number) { }

    public function render(bool $hidden = false): string
    {
        return view($hidden ? 'hidden-card' : 'card', [
            'color' => $this->color,
            'number' => $this->number,
        ])->render();
    }
}
