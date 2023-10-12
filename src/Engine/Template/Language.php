<?php

namespace WatchNext\Engine\Template;

class Language {
    private static ?array $config = null;
    private static array $translations = [];
    private string $lang;

    public function __construct() {
        if (self::$config !== null) {
            return;
        }

        self::$config = require __DIR__ . '/../../../config/translation.php';
        $this->setLang($_SESSION['kernel.lang'] ?? self::$config['defaultLanguage']);
    }

    public function setLang(string $lang): void {
        $this->lang = $lang;

        $translationFilePath = __DIR__ . "/../../../config/tranlsations/messages.{$this->lang}.php";

        if (!file_exists($translationFilePath)) {
            throw new \Exception("There no messages.{$this->lang}.php file!");
        }

        self::$translations = require $translationFilePath;
    }

    public function trans(string $key): string {
        return self::$translations[$key] ?? '';
    }

    public function getLang(): string {
        return $this->lang;
    }
}