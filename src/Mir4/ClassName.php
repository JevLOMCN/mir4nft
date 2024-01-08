<?php

namespace RPurinton\Mir4nft\Mir4;

class ClassName
{
    public static function get(int $class_id): string
    {
        return match ($class_id) {
            1 => 'Warrior',
            2 => 'Sorcerer',
            3 => 'Taoist',
            4 => 'Arbalist',
            5 => 'Lancer',
            6 => 'Darkist',
            default => 'Unknown'
        };
    }
}
