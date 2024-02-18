<?php

namespace Penguin\Component\Container;

class ServiceProviderManager
{
    protected array $providers = [];

    public function __construct(protected ContainerInterface $container) {}

    public function register(string $provider): void
    {
        if (!in_array($provider, $this->providers)) {
            $this->providers[] = $provider;
        }
    }

    public function boot(): void
    {
        foreach ($this->providers as &$provider) {
            $provider = new $provider();
            if (method_exists($provider, 'register')) {
                $provider->register($this->container);
            }
        }

        foreach ($this->providers as $provider) {
            if (method_exists($provider, 'boot')) {
                BoundMethod::call([$provider, 'boot']);
            }
        }
    }
}