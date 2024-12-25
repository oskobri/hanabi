<?php

namespace App;

use App\ValueObjects\Hint;
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

    public function giveHint(Hint $hint): void
    {
        $knownProperty = 'known' . ucfirst($hint->type->value);

        $this->cards
            ->where("{$hint->type->value}.value", $hint->value)
            ->each(fn($card) => $card->$knownProperty = true);
    }

    public function renderCards(bool $hidden = false, bool $displayName = true): void
    {
        render(
            view('player-hand', [
                'name' => $this->name,
                'cards' => $this->cards,
                'hidden' => env('SHOW_HIDDEN_CARDS') ? false : $hidden,
                'displayName' => $displayName
            ])
        );
    }
}
