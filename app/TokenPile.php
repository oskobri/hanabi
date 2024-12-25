<?php

namespace App;

use function Termwind\render;

class TokenPile
{
    const int MAX_TOKEN = 8;

    public int $tokensCount;

    public function __construct()
    {
        $this->tokensCount = static::MAX_TOKEN;
    }

    public function isEmpty(): bool
    {
        return $this->tokensCount === 0;
    }

    public function isFull(): bool
    {
        return $this->tokensCount === static::MAX_TOKEN;
    }

    public function putToken(): void
    {
        if (!$this->isFull()) {
            $this->tokensCount++;
        }
    }

    public function removeToken(): void
    {
        if (!$this->isEmpty()) {
            $this->tokensCount--;
        }
    }

    public function render(): void
    {
        $tokenText = str('token')->plural($this->tokensCount);

        render(<<<HTML
            <div class="mb-1">
                <h1>Token Pile</h1>
                <div>{$this->tokensCount} remaining $tokenText</div>
            </div>
        HTML
        );
    }
}
