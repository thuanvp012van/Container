<?php

namespace Penguin\Component\Container;

use Penguin\Component\Container\Exception\ServiceNotFoundException;
use Penguin\Component\Container\Exception\MethodNotFoundException;
use ReflectionParameter;
use ReflectionMethod;

/**
 * Container is a dependency injection container.
 * 
 * @author Nguyễn Hoàng Thắng Thuận <thuanvp012van@gmail.com>
 */
class Container implements ContainerInterface
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

    /**
     * Singletons constructor should always be private.
     */
    protected function __construct() {}

    /**
     * Get the globally available instance of the container.
     * 
     * @return static
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
     * Check service exists.
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

    /**
     * Call method in service.
     * 
     * @param string $method
     * @param int $id
     * @param array $arguments
     * @return mixed
     */
    public function call(string $method, string $id, array $arguments = []): mixed
    {
        if ($this->has($id)) {
            $definition = $this->definitions[$id];
            $methodCall = $definition->getMethodCall($method);
            if ($methodCall !== null) {
                $service = $this->get($id);
                $arguments = array_map(function ($argument) use ($arguments) {
                    if ($argument instanceof ReflectionParameter) {
                        $argName = $argument->getName();
                        if (array_key_exists($argName, $arguments)) {
                            return $arguments[$argName];
                        }
                    }
                    return $argument;
                }, $methodCall[$method]);
                return $service->$method(...$this->extractArguments($service, $method, $arguments));
            }
            throw new MethodNotFoundException("Method {$definition->getClass()}::{$method}() does not exist");
        }
        throw new ServiceNotFoundException("Service {$id} does not exist");
    }

    /**
     * Set alias for the service.
     * 
     * @param string $id
     * @param string $alias
     * @return static
     */
    public function setAlias(string $id, string $alias): static
    {
        $this->aliasDefinitions[$alias] = $id;
        return $this;
    }

    /**
     * Get service definitions.
     * 
     * @return array<string, \Penguin\Component\Container\Definition>
     */
    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    /**
     * Create and return a service.
     * 
     * @param string $id
     * @return object|false
     */
    protected function make(string $id): object|false
    {
        $definition = null;
        if (isset($this->definitions[$id])) {
            $definition = $this->definitions[$id];
        }

        if (isset($this->aliasDefinitions[$id])) {
            $id = $this->aliasDefinitions[$id];
            $definition = $this->definitions[$id];
        }

        if ($definition === null) {
            return false;
        }

        $class = $definition->getClass();
        $arguments = $this->extractArguments($class, '__construct', $definition->getArguments());
        $service = new $class(...$arguments);

        if ($definition->isSingleton()) {
            $this->services[$id] = $service;
        }

        return $service;
    }

    /**
     * Get service by tag.
     * 
     * @param Tag $tag
     * @return object[]|object
     */
    protected function getServicesByTag(Tag $tag): array|object
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

    /**
     * Extract arguments.
     * 
     * @param object|string $objectOrClass
     * @param string $method
     * @param mixed[] $arguments
     * @return object[]
     */
    protected function extractArguments(object|string $objectOrClass, string $method, array $arguments): array
    {
        $results = [];
        foreach ($arguments as $argument) {
            if ($argument instanceof Reference) {
                $argument = $this->get((string) $argument);
            }

            if (!$argument instanceof Tag) {
                $results[] = $argument;
            } else {
                $services = $this->getServicesByTag($argument);
                $params = (new ReflectionMethod($objectOrClass, $method))->getParameters();
                foreach ($params as $position => $param) {
                    $argument = $services[$param->getType()->getName()][0];
                    if (isset($argument)) {
                        $results[$position] = $argument;
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Singletons should not be cloneable.
     */
    protected function __clone(): void {}

    /**
     * Singletons should not be restorable from strings.
     */
    public function __wakeup(): void
    {
        throw new \Exception('Cannot unserialize a singleton.');
    }
}
