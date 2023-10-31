<?php

namespace WatchNext\Engine\Template;

use WatchNext\Engine\Config;

class Language
{
    private static ?array $translationConfig = null;
    private static array $translations = [];
    private static string $lang;

    public function __construct(private readonly Config $config)
    {
        if (self::$translationConfig !== null) {
            return;
        }

        self::$translationConfig = $config->get('translation.php');
        $this->setLang($_SESSION['kernel.lang'] ?? self::$translationConfig['defaultLanguage']);
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
}
