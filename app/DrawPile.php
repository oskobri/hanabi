<?php

declare(strict_types=1);

namespace App;

use App\Enums\Color;

use App\Enums\Number;
use Illuminate\Support\Collection;
use function Termwind\render;

final class DrawPile
{
    public Collection $cards;

    public function __construct()
    {
        $this->cards = collect();

        $this->buildPile();
    }

    public function pick(): ?Card
    {
        return $this->cards->shift();
    }

    public function isEmpty(): bool
    {
        return $this->cards->isEmpty();
    }

    public function renderRemainingCards(): void
    {
        render(<<<HTML
            <div class="mb-1">
                <h1>Card deck</h1>
                <div>{$this->cards->count()} remaining cards</div>
            </div>
        HTML);
    }

    private function buildPile(): void
    {
        foreach (Color::cases() as $color) {
            foreach (Number::cases() as $number) {
                foreach(range(1, $number->getOccurences()) as $occurence) {
                    $this->cards->push(new Card($color, $number));
                }
            }
        }

        $this->cards = $this->cards->shuffle();
    }

}
