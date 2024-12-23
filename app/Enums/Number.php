<?php

namespace App\Enums;

enum Number: int
{
    case One   = 1;
    case Two   = 2;
    case Three = 3;
    case Four  = 4;
    case Five  = 5;

    public function getOccurences(): int
    {
        return match ($this) {
            self::One                               => 3,
            self::Two, self::Three, self::Four      => 2,
            self::Five                              => 1,
        };
    }
}
