<?php

use Penguin\Component\Container\Container;

if (!function_exists('service')) {
    /**
     * Get service by abtract.
     */
    function service(string $id = null): object|false
    {
        $container = Container::getInstance();
        return $id === null ? $container : $container->get($id);
    }
}