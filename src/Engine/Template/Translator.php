<?php

namespace WatchNext\Engine\Template;

use WatchNext\Engine\Config;
use WatchNext\WatchNext\Domain\User\LanguageEnum;
use WatchNext\WatchNext\Domain\User\User;
use WatchNext\WatchNext\Domain\User\UserRepository;

class Translator
{
    private static ?array $translationConfig = null;
    private static array $translations = [];
    private static string $lang;

    public function __construct(private readonly Config $config, private readonly UserRepository $userRepository)
    {
        if (self::$translationConfig !== null) {
            return;
        }

        self::$translationConfig = $config->get('translation.php');
    }

    public function init(?User $user): void
    {
        $userLang = $user?->getLanguage()?->value;
        $cookieLang = $_COOKIE['lang'] ?? null;
        $defaultLang = self::$translationConfig['defaultLanguage'];

        if ($cookieLang && $userLang && $cookieLang !== $userLang) {
            $user->setLanguage(LanguageEnum::from($cookieLang));
            $this->userRepository->save($user);
            $userLang = $cookieLang;
        }

        if ($userLang) {
            $this->setLang($userLang);
            return;
        }

        if ($cookieLang) {
            $this->setLang($cookieLang);
            return;
        }

        $this->setLang($defaultLang);
    }

    public function setLang(string $lang): void
    {
        self::$lang = $lang;
        self::$translations = $this->config->get("translations/messages.{$lang}.php");
    }

    public function trans(string $key, array $params = []): string
    {
        $translation = self::$translations[$key] ?? $key;

        foreach ($params as $paramKey => $paramValue) {
            $translation = str_replace($paramKey, $paramValue, $translation);
        }

        return $translation;
    }

    public function getLang(): string
    {
        return self::$lang;
    }

    public function getLangEmoji(): string
    {
        return LanguageEnum::from(self::$lang)->getEmoji();
    }

    public function getAvailableLangs(): array
    {
        return array_filter(LanguageEnum::cases(), fn (LanguageEnum $lang) => $lang->value !== self::$lang);
    }
}
