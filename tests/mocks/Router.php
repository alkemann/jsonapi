<?php

namespace alkemann\jsonapi\tests\mocks;

class Router
{
    public static $routes = [];
    public static $DELIMITER = '|';

    public static function add(string $url, callable $cb, string $method): void
    {
        static::$routes[] = func_get_args();
    }
}