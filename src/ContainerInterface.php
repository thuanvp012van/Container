<?php

namespace Penguin\Component\Container;

use Penguin\Component\Container\Definition;

interface ContainerInterface
{
    /**
     * Get the globally available instance of the container.
     */
    public static function getInstance(): static;

    /**
     * Register a service.
     * 
     * @param string $id
     * @param string $concrete
     * @return \Penguin\Component\Container\Definition
     */
    public function register(string $id, string $concrete): Definition;

    /**
     * Get service by id.
     * 
     * @param string $id
     * @return object|false
     */
    public function get(string $id): object|false;

    /**
     * Check service exists
     * 
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool;

    public function call(string $method, string $id): mixed;

    /**
     * Set alias call service.
     */
    public function setAlias(string $id, string $alias): static;

    /**
     * Singletons should not be restorable from strings.
     */
    public function __wakeup();
}