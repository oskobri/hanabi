<?php

namespace App;

use Illuminate\Support\Collection;
use function Termwind\render;

class DiscardPile
{
    /**
     * @var Collection<int, Card> $cards
     */
    public Collection $cards;

    public function __construct()
    {
        $this->cards = collect();
    }

    public function pushCard(Card $card): void
    {
        $this->cards->push($card);
    }

    public function render(): void
    {
        render(
            view('discard-pile', [
                'cards' => $this->cards
            ])
        );
    }
}
