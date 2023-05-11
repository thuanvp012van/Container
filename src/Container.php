<?php

namespace Penguin\Component\Container;

use ReflectionMethod;

/**
 * Container is a dependency injection container.
 * 
 * @author Nguyễn Hoàng Thắng Thuận <thuanvp012van@gmail.com>
 */
class Container
{
    private static $instance = null;

    /**
     * @var array<string, object>
     */
    protected array $services = [];

    /**
     * @var array<string, \Penguin\Component\Container\Definition>
     */
    protected array $definitions = [];

    /**
     * @var string[]
     */
    protected array $aliasDefinitions = [];

    protected function __construct() {}

    /**
     * Get the globally available instance of the container.
     */
    public static function getInstance(): static
    {
        if (static::$instance === null) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    /**
     * Register a service.
     * 
     * @param string $id
     * @param string $concrete
     * @return \Penguin\Component\Container\Definition
     */
    public function register(string $id, string $concrete): Definition
    {
        $definition = new Definition($id, $concrete);
        $this->definitions[$id] = $definition;
        return $definition;
    }

    /**
     * Get service by id.
     * 
     * @param string $id
     * @return object|false
     */
    public function get(string $id): object|false
    {
        if (isset($this->services[$id])) {
            return $this->services[$id];
        }

        if (isset($this->aliasDefinitions[$id])) {
            $id = $this->aliasDefinitions[$id];
            if (isset($this->services[$id])) {
                return $this->services[$id];
            }
        }

        return $this->make($id);
    }

    /**
     * Check service exists
     * 
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->services[$id])
            || isset($this->definitions[$id])
            || isset($this->aliasDefinitions[$id]);
    }

    public function setAlias(string $id, string $alias): static
    {
        $this->aliasDefinitions[$alias] = $id;
        return $this;
    }

    /**
     * Create and return a service.
     */
    protected function make(string $id): object|false
    {
        $definition = null;
        if (isset($this->definitions[$id])) {
            $definition = $this->definitions[$id];
        }

        if (isset($this->aliasDefinitions[$id])) {
            $alias = $this->aliasDefinitions[$id];
            $definition = $this->definitions[$alias];
        }

        if ($definition === null) {
            return false;
        }

        $class = $definition->getClass();

        $arguments = [];
        foreach ($definition->getArguments() as $argument) {
            if ($argument instanceof Reference) {
                $argument = $this->get((string) $argument);
            }
            
            if (!$argument instanceof Tag) {
                $arguments[] = $argument;
            } else {
                $services = $this->getServicesByTag($argument);

                $params = (new ReflectionMethod($class, '__construct'))->getParameters();
                foreach ($params as $position => $param) {
                    $argument = $services[$param->getType()->getName()][0];
                    if (isset($argument)) {
                        $arguments[$position] = $argument;
                    }
                }
            }
        }

        $service = new $class(...$arguments);

        if ($definition->isSingleton()) {
            $this->addService($id, $service);
        }

        return $service;
    }

    public function getServicesByTag(Tag $tag): array|object
    {
        $tag = (string) $tag;
        $services = [];
        foreach ($this->definitions as $id => $definition) {
            $tags = $definition->getTags();
            if (in_array($tag, $tags)) {
                $abstract = $definition->hasAbstract() ? $definition->getAbStract() : $definition->getClass();
                $services[$abstract][] = $this->get($id);
            }
        }
        return $services;
    }

    protected function addService(string $id, object $service): static
    {
        $this->services[$id] = $service;
        return $this;
    }

    /**
     * Singletons should not be cloneable.
     */
    protected function __clone() {}

    /**
     * Singletons should not be restorable from strings.
     */
    public function __wakeup()
    {
        throw new \Exception('Cannot unserialize a singleton.');
    }
}
