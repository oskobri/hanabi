<?php

namespace App;

use Illuminate\Support\Collection;
use function Termwind\render;

class Discard
{
    /**
     * @var Collection<int, Card>
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
            view('discard', [
                'cards' => $this->cards
            ])
        );
    }
}
