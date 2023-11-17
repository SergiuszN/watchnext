<?php

namespace WatchNext\Engine\Enum;

trait EnumEnhancements
{
    public static function getValues(): array
    {
        return array_map(fn ($enum) => $enum->value, self::cases());
    }

    public static function contains(mixed $value): bool
    {
        return in_array($value, self::getValues(), true);
    }
}
