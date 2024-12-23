<?php

namespace App;

use Illuminate\Support\Collection;
use function Termwind\render;

final class Player
{
    /**
     * @var Collection<int, Card> $cards
     */
    public Collection $cards;

    public function __construct(public string $name)
    {
        $this->cards = collect();
    }

    public function giveCard(Card $card): void
    {
        $this->cards = $this->cards->values();
        $this->cards->push($card);
    }

    public function pull(int $cardIndex): Card
    {
        $card = $this->cards->pull($cardIndex);
        $this->cards = $this->cards->values();

        return $card;
    }

    public function renderCards(bool $hidden = false, bool $displayName = true): void
    {
        render(
            view('player.hand', [
                'name' => $this->name,
                'cards' => $this->cards,
                'hidden' => env('SHOW_HIDDEN_CARDS') ? false : $hidden,
                'displayName' => $displayName
            ])
        );
    }
}
