<?php

namespace Penguin\Component\Container;

use Penguin\Component\Container\Exception\ServiceNotFoundException;

class Container implements ContainerInterface
{
    private static ?ContainerInterface $instance = null;

    /**
     * @var array<string, object>
     */
    protected array $bindings = [];

    /**
     * @var array<string, object>
     */
    protected array $singletionInstances = [];


    /**
     * @var array<string, object>
     */
    protected array $scopedInstances = [];

    /**
     * @var string[]
     */
    protected array $aliases = [];

    /**
     * Singletons constructor should always be private.
     */
    protected function __construct()
    {
    }

    /**
     * Get the globally available instance of the container.
     */
    public static function getInstance(): static
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
            static::$instance->singleton('container', function () {
                return static::$instance;
            });
            static::$instance->alias('container', ContainerInterface::class);
        }
        return static::$instance;
    }

    /**
     * Register a shared binding in the container.
     */
    public function singleton(string $id, callable $callback): void
    {
        $this->bindings[__FUNCTION__][$id] = $callback;
    }

    /**
     * Register a shared binding if it hasn't already been registered.
     */
    public function singletonIf(string $id, callable $callback): void
    {
        if (!$this->has($id)) {
            $this->singleton($id, $callback);
        }
    }

    /**
     * Register a transient binding in the container.
     */
    public function transient(string $id, callable $callback): void
    {
        $this->bindings[__FUNCTION__][$id] = $callback;
    }

    /**
     * Register a transient binding if it hasn't already been registered.
     */
    public function transientIf(string $id, callable $callback): void
    {
        if (!$this->has($id)) {
            $this->transient($id, $callback);
        }
    }

    /**
     * Register a scoped binding in the container.
     */
    public function scoped(string $id, callable $callback): void
    {
        $this->bindings[__FUNCTION__][$id] = $callback;
    }

    /**
     * Register a scoped binding if it hasn't already been registered.
     */
    public function scopedIf(string $id, callable $callback): void
    {
        if (!$this->has($id)) {
            $this->scoped($id, $callback);
        }
    }

    /**
     * Get service by id.
     */
    public function get(string $id): array|object
    {
        if (!$this->has($id)) {
            throw new ServiceNotFoundException("Service $id does not exist");
        }

        if (isset($this->aliases[$id])) {
            $id = $this->aliases[$id];
        }

        if (isset($this->singletionInstances[$id])) {
            return $this->singletionInstances[$id];
        }

        if (isset($this->scopedInstances[$id])) {
            return $this->scopedInstances[$id];
        }

        return $this->make($id);
    }

    /**
     * Check service exists.
     */
    public function has(string $id): bool
    {
        return isset($this->bindings['singleton'][$id])
            || isset($this->bindings['transient'][$id])
            || isset($this->bindings['scoped'][$id])
            || isset($this->aliases[$id]);
    }

    /**
     * Alias a type to a different name.
     */
    public function alias(string $id, string $alias): static
    {
        if ($alias === $id) {
            throw new \LogicException("[{$id}] is aliased to itself.");
        }
        $this->aliases[$alias] = $id;
        return $this;
    }

    /**
     * Get the alias for an abstract if available.
     */
    public function getAlias(string $id): array
    {
        $aliases = [];
        foreach ($this->aliases as $alias => $serviceId) {
            if ($id === $serviceId) {
                $aliases[] = $alias;
            }
        }
        return $aliases;
    }

    /**
     * Clear all of the scoped instances from the container.
     */
    public function forgetScopedInstances(): void
    {
        foreach (array_keys($this->scopedInstances) as $id) {
            unset($this->scopedInstances[$id]);
        }
    }

    /**
     * Create and return one or more services.
     */
    protected function make(string $id): array|object
    {
        if (!empty($this->bindings['singleton'][$id])) {
            $service = $this->bindings['singleton'][$id]($this);
            $this->singletionInstances[$id] = $service;
        } else if (!empty($this->bindings['scoped'][$id])) {
            $service = $this->bindings['scoped'][$id]($this);
            $this->scopedInstances[$id] = $service;
        } else {
            $service = $this->bindings['transient'][$id]($this);
        }

        InjectProperties::handle($service);
        return $service;
    }

    /**
     * Singletons should not be cloneable.
     */
    protected function __clone(): void
    {
    }

    /**
     * Singletons should not be restorable from strings.
     */
    public function __wakeup(): void
    {
        throw new \Exception('Cannot unserialize a singleton.');
    }
}
