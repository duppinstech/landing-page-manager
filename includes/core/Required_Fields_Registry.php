<?php
namespace LPManager\core;

class Required_Fields_Registry {
    private static $fields = [];

    public static function register($template, array $fields) {
        if (!isset(self::$fields[$template])) {
            self::$fields[$template] = [];
        }
        self::$fields[$template] = array_merge(self::$fields[$template], $fields);
    }

    public static function get($template) {
        return self::$fields[$template] ?? [];
    }
}