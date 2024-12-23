<?php

namespace App;

use App\Enums\Color;
use Illuminate\Support\Collection;
use function Termwind\render;

class PlayedCards
{
    /**
     * @var Collection<string, Collection<int, Card>> $cards
     */
    public Collection $cards;

    public function __construct()
    {
        $this->cards = collect();

        foreach (Color::cases() as $color) {
            $this->cards->{$color->value} = collect();
        }
    }

    public function render(): void
    {
        render(
            view('played-cards', [
                'cards' => $this->cards,
            ])
        );
    }
}
