<?php

namespace Penguin\Component\Container\Loader;

/**
 * ClosureLoader loads service definitions from a PHP closure.
 *
 * The Closure has access to the container as its first argument.
 *
 * @author Nguyễn Hoàng Thắng Thuận <thuanvp012van@gmail.com>
 */
class ClosureLoader extends BaseLoader
{
    public function load(mixed $resource): void
    {
        $resource($this->container);
    }
}