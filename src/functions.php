<?php

use Penguin\Component\Container\Container;

if (!function_exists('service')) {
    /**
     * Get service by id.
     */
    function service(string $id = null): object
    {
        $container = Container::getInstance();
        return is_null($id) ? $container : $container->get($id);
    }
}