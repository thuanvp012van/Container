<?php

namespace Penguin\Component\Container\Loader;

use Penguin\Component\Container\Container;

abstract class BaseLoader
{
    public function __construct(protected Container $container) {}

    abstract public function load(mixed $resource);
}