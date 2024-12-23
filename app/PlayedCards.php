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
            $this->cards->put($color->value, collect());
        }
    }

    public function render(): void
    {
        render(
            view('played-cards', [
                'cards' => $this->cards,
                'isEmpty' => $this->isEmpty(),
            ])
        );
    }

    public function isEmpty(): bool
    {
        return $this->cards->filter(fn(Collection $cards) => $cards->isNotEmpty())->isEmpty();
    }

    public function add(Card $card): void
    {
        $this->cards->get($card->color->value)->push($card);
    }

    public function countForColor(Color $color): int
    {

        return $this->cards->get($color->value)->count();
    }
}
