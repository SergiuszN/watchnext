<?php

namespace WatchNext\WatchNext\Domain\User;

use WatchNext\Engine\Enum\EnumEnhancements;

enum LanguageEnum: string
{
    use EnumEnhancements;

    case EN = 'en';
    case PL = 'pl';
    case UA = 'ua';

    public function getEmoji(): string
    {
        return match ($this) {
            self::EN => '🇬🇧',
            self::PL => '🇵🇱',
            self::UA => '🇺🇦',
        };
    }
}
