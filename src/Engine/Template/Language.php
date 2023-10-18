<?php

namespace WatchNext\Engine\Template;

use WatchNext\Engine\Config;

class Language {
    private static ?array $config = null;
    private static array $translations = [];
    private string $lang;

    public function __construct() {
        if (self::$config !== null) {
            return;
        }

        self::$config = (new Config())->get('translation.php');
        $this->setLang($_SESSION['kernel.lang'] ?? self::$config['defaultLanguage']);
    }

    public function setLang(string $lang): void {
        $this->lang = $lang;
        self::$translations = (new Config())->get("translations/messages.{$this->lang}.php");
    }

    public function trans(string $key, array $params = []): string {
        $translation = self::$translations[$key] ?? $key;

        foreach ($params as $paramKey => $paramValue) {
            $translation = str_replace($paramKey, $paramValue, $translation);
        }

        return $translation;
    }

    public function getLang(): string {
        return $this->lang;
    }
}