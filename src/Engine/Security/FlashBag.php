<?php

namespace WatchNext\Engine\Security;

use InvalidArgumentException;

class FlashBag
{
    private const KEY = 'main.flash.bag';

    public function __construct()
    {
        if (!isset($_SESSION[self::KEY])) {
            $_SESSION[self::KEY] = [];
        }
    }

    public function add(string $label, string $message): void
    {
        $_SESSION[self::KEY][] = ['label' => $label, 'message' => $message];
    }

    public function addValidationErrors(InvalidArgumentException $invalidArgumentException): void
    {
        [$input, $message] = explode(':', $invalidArgumentException->getMessage(), 2);
        $this->add("error.$input", $message);
    }

    public function getAll(): array
    {
        $flashes = $_SESSION[self::KEY];
        $_SESSION[self::KEY] = [];

        return $flashes;
    }

    public function getAllByLabel(string $label): array
    {
        $flashes = [];

        foreach ($_SESSION[self::KEY] as $key => $flash) {
            if ($flash['label'] === $label) {
                $flashes[] = $flash;
                unset($_SESSION[self::KEY][$key]);
            }
        }

        return $flashes;
    }

    public function get(string $label, string $default = ''): string
    {
        foreach ($_SESSION[self::KEY] as $key => $flash) {
            if ($flash['label'] === $label) {
                unset($_SESSION[self::KEY][$key]);

                return $flash['message'];
            }
        }

        return $default;
    }

    public function getAllByLabels(array $labels): array
    {
        $flashes = [];

        foreach ($_SESSION[self::KEY] as $key => $flash) {
            if (in_array($flash['label'], $labels)) {
                $flashes[] = $flash;
                unset($_SESSION[self::KEY][$key]);
            }
        }

        return $flashes;
    }
}
