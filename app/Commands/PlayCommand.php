<?php

namespace App\Commands;

use App\GameSession;
use App\Player;
use Illuminate\Support\Collection;
use LaravelZero\Framework\Commands\Command;

class PlayCommand extends Command
{
    protected $signature = 'play {--players-count=} {--default-players-name}';

    protected $description = 'Play a game of hanabi';

    private GameSession $gameSession;

    public function handle(): void
    {
        $players = $this->getPlayers();

        if ($players->isEmpty()) {
            $this->info('See u next time :)');
        }

        $this->gameSession = new GameSession($players);

        while (!$this->gameSession->isOver) {
            $this->turn();

            $this->gameSession->nextPlayer();
        }

        if ($this->gameSession->isGameLost()) {
            $this->gameLost();
            return;
        }

        $this->info('You won !');
    }

    protected function turn(): void
    {
        $currentPlayer = $this->gameSession->getCurrentPlayer();

        $this->gameSession->renderOtherPlayersCards();
        $this->gameSession->discardPile->render();
        $this->gameSession->playedCards->render();
        $this->gameSession->drawPile->renderRemainingCards();

        $this->info("Your turn, $currentPlayer->name");
        $currentPlayer->renderCards(!env('SHOW_HIDDEN_CARDS'), false);

        $this->doAction();

        if ($this->gameSession->lastPlayerToPlay()?->name === $currentPlayer->name) {
            $this->gameSession->isOver = true;
        }
    }

    protected function doAction(): void
    {
        $choice = $this->choice('What action do you want to do ?', [
            'Give a hint',
            'Discard',
            'Play a card',
        ]);

        match ($choice) {
            'Give a hint' => $this->giveHint(),
            'Discard' => $this->discard(),
            'Play a card' => $this->play(),
        };
    }

    private function discard(): void
    {

        $cardIndexToDiscard = $this->chooseCard('What card do you want to discard ?');

        $this->gameSession->discard($cardIndexToDiscard);

        if (($card = $this->gameSession->drawCard())) {
            $this->gameSession
                ->getCurrentPlayer()
                ->giveCard($card, 4);
        }
    }

    private function play()
    {
        $cardIndexToDiscard = $this->chooseCard('What card do you want to play ?');

    }

    private function giveHint()
    {

    }

    protected function gameLost(): void
    {
        $this->info('Game over...');

        $restart = $this->choice('Want to play another game ?', [
            'Yes',
            'No'
        ]);

        if ($restart === 'Yes') {
            $this->handle();
        }
    }

    private function chooseCard(string $message): int
    {
        $player = $this->gameSession->getCurrentPlayer();
        $player->renderCards(true);

        $choices = [
            'Card position 1',
            'Card position 2',
            'Card position 3',
            'Card position 4',
            'Card position 5',
        ];

        $choice = $this->choice($message, $choices);

        return array_search($choice, $choices);
    }

    private function getPlayers(): Collection
    {
        $playersCount = $this->option('players-count') ?? $this->choice('How many players ? ', [
            2 => '2',
            3 => '3',
            4 => '4',
            5 => '5'
        ]);

        if (!$playersCount) {
            return collect();
        }

        $players = collect();

        foreach (range(1, $playersCount) as $playerNumber) {
            $name = $this->option('default-players-name')
                ? "Player $playerNumber"
                : $this->ask("What is the name of the player n° $playerNumber", "Player $playerNumber");
            $players->push(new Player($name));
        }

        return $players;
    }
}
