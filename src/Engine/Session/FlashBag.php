<?php

namespace WatchNext\Engine\Session;

use InvalidArgumentException;

class FlashBag {
    private const KEY = 'main.flash.bag';

    public function add(string $label, string $message): void {
        $this->init();

        $_SESSION[self::KEY][] = ['label' => $label, 'message' => $message];
    }

    public function addValidationErrors(InvalidArgumentException $invalidArgumentException): void {
        [$input, $message] = explode(':', $invalidArgumentException->getMessage(), 2);
        $this->add("error.$input", $message);
    }

    public function getAll(): array {
        $this->init();

        $flashes = $_SESSION[self::KEY];
        $_SESSION[self::KEY] = [];

        return $flashes;
    }

    public function getAllByLabel(string $label): array {
        $this->init();

        $flashes = [];

        foreach ($_SESSION[self::KEY] as $key => $flash) {
            if ($flash['label'] === $label) {
                $flashes[] = $flash;
                unset($_SESSION[self::KEY][$key]);
            }
        }

        return $flashes;
    }

    public function getAllByLabels(array $labels): array {
        $this->init();

        $flashes = [];

        foreach ($_SESSION[self::KEY] as $key => $flash) {
            if (in_array($flash['label'], $labels)) {
                $flashes[] = $flash;
                unset($_SESSION[self::KEY][$key]);
            }
        }

        return $flashes;
    }

    private function init(): void {
        if (!isset($_SESSION[self::KEY])) {
            $_SESSION[self::KEY] = [];
        }
    }
}