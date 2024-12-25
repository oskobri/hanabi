<?php

namespace App\Commands;

use App\Enums\HintType;
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
        if(!$this->validateOptions()) {
            return;
        }

        $players = $this->getPlayers();

        if ($players->isEmpty()) {
            $this->info('See u next time :)');
        }

        $this->gameSession = new GameSession($players);

        while (!$this->gameSession->isOver) {
            $this->info('-------------------------------------------------------');
            $this->info('--------------------- Next Player ---------------------');
            $this->info('-------------------------------------------------------');

            $this->turn();

            $this->gameSession->nextPlayer();
        }

        if ($this->gameSession->isGameLost()) {
            $this->gameLost();
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
            'Give a hint' => $this->giveHint(),
            'Discard' => $this->discard(),
            'Play a card' => $this->play(),
        };
    }

    private function discard(): void
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

    private function play(): void
    {
        $currentPlayer = $this->gameSession->getCurrentPlayer();

        $cardIndexToDiscard = $this->chooseCard($currentPlayer, 'What card do you want to play ?');

        if (!$this->gameSession->play($cardIndexToDiscard)) {
            $this->warn('Error! You cannot play, this card');
        }
    }

    private function giveHint(): void
    {
        $numberPrefix = "Number ";

        $otherPlayer = $this->choicePlayerToGiveHint();
        $otherPlayer->renderCards();

        $choices = collect()
            ->merge($otherPlayer->cards->pluck('number.value')->transform(fn ($number) => $numberPrefix . $number)->unique())
            ->merge($otherPlayer->cards->pluck('color.value')->unique())
            ->values()
            ->toArray();

        $hintChoice = $this->choice(question: 'What hint do you want to give ?', choices: $choices);

        if (str($hintChoice)->startsWith($numberPrefix)) {
            $hintType = HintType::Number;
            $value = str($hintChoice)->replace($numberPrefix, '')->toString();
        } else {
            $hintType = HintType::Color;
            $value = $hintChoice;
        }

        $otherPlayer->giveHint($hintType, $value);
        $this->gameSession->tokenPile->removeToken();
    }

    private function gameLost(): void
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

    private function chooseCard(Player $player, string $question, bool $multiple = false): int|array
    {
        $player->renderCards(true);

        $choices = [
            'Card 1',
            'Card 2',
            'Card 3',
            'Card 4',
            'Card 5',
        ];

        $selectedChoices = $this->choice(question: $question, choices: $choices, multiple: $multiple);

        if (!$multiple) {
            return array_search($selectedChoices, $choices);
        }

        $cardIndexes = [];

        foreach ($selectedChoices as $selectedChoice) {
            $cardIndexes[] = array_search($selectedChoice, $choices);
        }

        return $cardIndexes;
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

    private function choicePlayerToGiveHint(): Player
    {
        $otherPlayers = $this->gameSession->getOtherPlayers();

        if ($otherPlayers->count() === 1) {
            return $otherPlayers->first();
        }

        $playerChoices = $otherPlayers->pluck('name')->toArray();

        $playerChoice = $this->choice(
            'Which player do you want to give a hint for?',
            $playerChoices
        );

        $otherPlayerIndex = array_search($playerChoice, $playerChoices);

        return $otherPlayers->get($otherPlayerIndex);
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
