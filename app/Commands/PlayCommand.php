<?php

namespace App\Commands;

use app\Commands\Traits\PlayInteractive;
use App\GameSession;
use LaravelZero\Framework\Commands\Command;

class PlayCommand extends Command
{
    use PlayInteractive;

    protected $signature = 'play {--players-count=} {--default-players-name}';

    protected $description = 'Play a game of hanabi';

    private GameSession $gameSession;

    public function handle(): void
    {
        if(!$this->validateOptions()) {
            return;
        }

        $players = $this->getPlayers();

        if ($players->isEmpty()) {
            $this->info('See u next time :)');
            return;
        }

        $this->gameSession = new GameSession($players);

        while (!$this->gameSession->isOver) {
            $this->turn();

            $this->gameSession->nextPlayer();
        }

        if ($this->gameSession->isGameLost()) {
            $this->info('Game over...');

            if ($this->askForRestart()) {
                $this->handle();
            }
            return;
        }

        $this->info('You won !');
    }

    private function turn(): void
    {
        $currentPlayer = $this->gameSession->getCurrentPlayer();

        $this->gameSession->renderOtherPlayersCards();
        $this->gameSession->discardPile->render();
        $this->gameSession->playedCards->render();
        $this->gameSession->drawPile->renderRemainingCards();
        $this->gameSession->tokenPile->render();
        $this->gameSession->renderErrors();

        $this->info("Your turn, $currentPlayer->name");
        $currentPlayer->renderCards(!env('SHOW_HIDDEN_CARDS'), false);

        $this->doAction();

        if ($this->gameSession->lastPlayerToPlay()?->name === $currentPlayer->name) {
            $this->gameSession->isOver = true;
        }
    }

    private function doAction(): void
    {
        $choices = [
            'Play a card',
            'Discard'
        ];

        if(!$this->gameSession->tokenPile->isEmpty()) {
            $choices[] = 'Give a hint';
        }

        $choice = $this->choice('What action do you want to do ?', $choices);

        match ($choice) {
            'Give a hint' => $this->giveHintAction(),
            'Discard' => $this->discardAction(),
            'Play a card' => $this->playAction(),
        };
    }

    private function discardAction(): void
    {
        $currentPlayer = $this->gameSession->getCurrentPlayer();

        $cardIndexToDiscard = $this->chooseCard(
            $currentPlayer,
            'What card do you want to discard ?'
        );

        $this->gameSession->discard($cardIndexToDiscard);

        if (($card = $this->gameSession->drawCard())) {
            $currentPlayer->giveCard($card);
        }

        $this->gameSession->tokenPile->putToken();
    }

    private function playAction(): void
    {
        $currentPlayer = $this->gameSession->getCurrentPlayer();

        $cardIndexToDiscard = $this->chooseCard($currentPlayer, 'What card do you want to play ?');

        if (!$this->gameSession->play($cardIndexToDiscard)) {
            $this->warn('Error! You cannot play, this card');
        }
    }

    private function giveHintAction(): void
    {
        $otherPlayers = $this->gameSession->getOtherPlayers();

        $otherPlayer = $otherPlayers->count() === 1
            ? $otherPlayers->first()
            : $this->askWhichPlayerToGiveHint($otherPlayers);

        $otherPlayer->renderCards();

        $hint = $this->askHint($otherPlayer->cards);
        $otherPlayer->giveHint($hint);
        $this->gameSession->tokenPile->removeToken();
    }

    private function validateOptions(): bool
    {
        $playersCount = $this->option('players-count');

        if ($playersCount && (!is_numeric($playersCount) || $playersCount < 2 || $playersCount > 5)) {
            $this->error('Invalid players count. Must be between 2 and 5.');
            return false;
        }

        return true;
    }
}
