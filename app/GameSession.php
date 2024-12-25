<?php

namespace App;

use App\Enums\Number;
use Illuminate\Support\Collection;
use function Termwind\render;

final class GameSession
{
    public DrawPile $drawPile;
    public DiscardPile $discardPile;
    public PlayedCards $playedCards;
    public Collection $players;

    public int $currentPlayerIndex = 0;
    public bool $isLastTurn = false;

    /**
     * Game is over when game is lost or won
     * @var bool
     */
    public bool $isOver = false;

    /**
     * Game is lost when errors reach 3
     * @var int
     */
    public int $errors = 0;

    /**
     * @param Collection<int, Player> $players
     */
    public function __construct(Collection $players)
    {
        $this->drawPile = new DrawPile;
        $this->discardPile = new DiscardPile;
        $this->playedCards = new PlayedCards;
        $this->players = $players;

        $this->distribute();
    }

    public function getCurrentPlayer(): Player
    {
        return $this->players->get($this->currentPlayerIndex);
    }

    /**
     * @return Collection<int, Player>
     */
    public function getOtherPlayers(): Collection
    {
        return $this->players->except($this->currentPlayerIndex);
    }

    public function nextPlayer(): void
    {
        if ($this->currentPlayerIndex === ($this->players->count() - 1)) {
            $this->currentPlayerIndex = 0;
        }
        else {
            $this->currentPlayerIndex += 1;
        }
    }

    public function renderOtherPlayersCards(): void
    {
        $this->players->except($this->currentPlayerIndex)->each(fn (Player $player) => $player->renderCards());
    }

    public function addError(): void
    {
        $this->errors++;

        if($this->isGameLost()) {
            $this->isOver = true;
        }
    }

    public function isGameLost(): bool
    {
        return $this->errors === 3;
    }

    public function lastPlayerToPlay(): ?Player
    {
        if (!$this->isLastTurn && $this->drawPile->isEmpty()) {
            $this->isLastTurn = true;
            return $this->players->get($this->currentPlayerIndex);
        }

        return null;
    }

    public function drawCard(): ?Card
    {
        if ($this->drawPile->isEmpty()) {
            return null;
        }

        return $this->drawPile->pick();
    }

    public function discard(int $cardIndexToDiscard): void
    {
        if ($this->drawPile->isEmpty()) {
            return;
        }

        $card = $this->getCurrentPlayer()->pull($cardIndexToDiscard);

        $this->discardPile->add($card);
    }

    public function play(int $cardIndexToPlay): bool
    {
        $card = $this->getCurrentPlayer()->pull($cardIndexToPlay);

        if ($this->cardCanBePlayed($card)) {
            $this->playedCards->add($card);
        }
        else {
            $this->discardPile->add($card);
            $this->addError();
            $error = true;
        }

        $card = $this->drawPile->pick();
        $this->getCurrentPlayer()->giveCard($card);

        return $error ?? false;
    }

    public function renderErrors(): void
    {
        $classError = $this->errors ? 'text-red' : 'text-green';
        $errorText =  str('error')->plural($this->errors);

        render(<<<HTML
            <div class="mb-1 $classError">{$this->errors} {$errorText}</div>
        HTML);
    }

    private function cardCanBePlayed(Card $card): bool
    {
        return ($card->number->value - 1) === $this->playedCards->countForColor($card->color);
    }

    private function distribute(): void
    {
        $cardsCount = $this->players->count() > 3 ? 4 : 5;

        $this->players
            ->each(fn ($player) => $this->drawPile->cards
                ->shift($cardsCount)
                ->each(fn ($card) => $player->giveCard($card)
                )
            );
    }
}
