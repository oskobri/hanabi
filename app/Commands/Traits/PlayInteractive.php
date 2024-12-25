<?php

namespace app\Commands\Traits;

use App\Enums\HintType;
use App\Player;
use App\ValueObjects\Hint;
use Illuminate\Support\Collection;

trait PlayInteractive
{
    /**
     * @return Collection<int, Player>
     */
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

    private function askHint(Collection $cards): Hint
    {
        $numberPrefix = "Number ";

        $choices = collect()
            ->merge($cards->pluck('number.value')->transform(fn ($number) => $numberPrefix . $number)->unique())
            ->merge($cards->pluck('color.value')->unique())
            ->values()
            ->toArray();

        $hintChoice = $this->choice(question: 'What hint do you want to give ?', choices: $choices);

        if (str($hintChoice)->startsWith($numberPrefix)) {
            return new Hint(HintType::Number, str($hintChoice)->replace($numberPrefix, '')->toString());
        }

        return new Hint(HintType::Color, $hintChoice);
    }

    private function askWhichPlayerToGiveHint(Collection $players)
    {
        $playerChoices = $players->pluck('name')->toArray();

        $playerChoice = $this->choice(
            'Which player do you want to give a hint for?',
            $playerChoices
        );

        $otherPlayerIndex = array_search($playerChoice, $playerChoices);

        return $players->get($otherPlayerIndex);
    }

    private function askForRestart(): bool
    {
        return $this->choice('Want to play another game ?', [
            'Yes',
            'No'
        ]) === 'Yes';
    }
}
