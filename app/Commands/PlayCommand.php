<?php

namespace App\Commands;

use App\Board;
use App\Player;
use Illuminate\Support\Collection;
use LaravelZero\Framework\Commands\Command;
use function Termwind\render;

class PlayCommand extends Command
{
    protected $signature = 'play {--players-count=} {--default-players-name}';

    protected $description = 'Play a game of hanabi';

    private Board $board;

    public function handle(): void
    {
        $players = $this->getPlayers();

        if ($players->isEmpty()) {
            $this->info('See u next time :)');
        }

        $this->board = new Board($players);
        $this->board->distribute();

        while (!$this->board->finished) {
            $this->turn();

            $this->board->nextPlayer();
        }

        if ($this->board->isGameLost()) {
            $this->gameLost();
            return;
        }

        $this->info('You won !');
    }

    protected function turn(): void
    {
        $currentPlayer = $this->board->getCurrentPlayer();

        $this->board->renderOtherPlayersCards();
        $this->board->discard->render();
        $this->board->playMat->render();
        $this->board->cardDeck->renderRemainingCards();

        $this->info("Your turn, $currentPlayer->name");
        $currentPlayer->renderCards(!env('SHOW_HIDDEN_CARDS'), false);


        $this->doAction();

        if ($this->board->lastPlayerToPlay()?->name === $currentPlayer->name) {
            $this->board->finished = true;
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

    private function getPlayers(): Collection
    {
        $players = collect();
        $playersCount = $this->option('players-count') ?? $this->getPlayersCount();

        if (!$playersCount) {
            return $players;
        }

        foreach (range(1, $playersCount) as $playerNumber) {
            $name = $this->option('default-players-name')
                ? "Player $playerNumber"
                : $this->ask("What is the name of the player n° $playerNumber", "Player $playerNumber");
            $players->push(new Player($name));
        }

        return $players;
    }

    private function getPlayersCount(): ?int
    {
        return $this->choice('How many players ? ', [
            2 => '2',
            3 => '3',
            4 => '4',
            5 => '5'
        ]);
    }

    private function giveHint()
    {

    }

    private function discard(): void
    {

        $cardIndexToDiscard = $this->chooseCard('What card do you want to discard ?');

        $this->board->discard($cardIndexToDiscard);

        if (($card = $this->board->drawCard())) {
            $this->board
                ->getCurrentPlayer()
                ->giveCard($card, 4);
        }
    }

    private function play()
    {
        $cardIndexToDiscard = $this->chooseCard('What card do you want to play ?');

    }

    private function chooseCard(string $message): int
    {
        $player = $this->board->getCurrentPlayer();
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
}
