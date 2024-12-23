<?php

namespace App;

use App\Enums\Color;
use Illuminate\Support\Collection;
use function Termwind\render;

/**
 * Played cards
 */
class PlayMat
{
    /**
     * @var Collection<string, Collection<int, Card>>
     */
    public Collection $cards;

    public function __construct()
    {
        $this->initPlayMat();
    }

    public function render(): void
    {
        render(
            view('play-mat', [
                'cards' => $this->cards,
            ])
        );
    }

    private function initPlayMat(): void
    {
        $this->cards = collect();

        foreach (Color::cases() as $color) {
            $this->cards->{$color->value} = collect();
        }
    }
}
