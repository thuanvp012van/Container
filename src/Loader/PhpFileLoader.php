<?php

namespace Penguin\Component\Container\Loader;

use Penguin\Component\Container\Definition;
use Penguin\Component\Container\Reference;
use ReflectionClass;

/**
 * PhpFileLoader loads service definitions from an array PHP file.
 *
 * @author Nguyễn Hoàng Thắng Thuận <thuanvp012van@gmail.com>
 */
class PhpFileLoader extends BaseLoader
{
    protected const SERVICE_KEYWORDS = [
        'arguments' => 'addArguments',
        'alias' => 'addAlias',
        'singleton' => 'singleton',
        'tags' => 'addTags',
        'calls' => 'setCallMethods',
        'autowire' => 'autowire'
    ];

    public function load(mixed $resource): void
    {
        $services = require $resource;
        foreach ($services as $id => $keywords) {
            $definition = $this->container->register($id, $keywords['class']);
            $this->setAbstract($definition, $keywords['class']);
            foreach ($keywords as $key => $value) {
                if ($key === 'arguments') {
                    $value = array_map(function ($argument) {
                        if (
                            strpos($argument, '@') === 0
                            && $this->container->has($service = substr($argument, 1))
                        ) {
                            return new Reference($service);
                        }
                        return $argument;
                    }, $value);
                }

                if (
                    isset(self::SERVICE_KEYWORDS[$key])
                    && !empty($keywords['autowire'])
                    && ($key !== 'arguments' || $key !== 'calls')
                ) {
                    $this->{self::SERVICE_KEYWORDS[$key]}($definition, $value);
                }
            }
        }
    }

    protected function setAbstract(Definition $definition, string $class): void
    {
        $interfaces = (new ReflectionClass($class))->getInterfaceNames();
        $abstract = empty($interfaces) ? $class : $interfaces[0];
        $definition->setAbstract($abstract);
    }

    protected function addArguments(Definition $definition, array $arguments): void
    {
        foreach ($arguments as $argument) {
            $definition->addArgument($argument);
        }
    }

    protected function addTags(Definition $definition, array $tags): void
    {
        foreach ($tags as $tag) {
            $definition->addTag($tag);
        }
    }

    protected function singleton(Definition $definition, bool $singleton): void
    {
        $definition->setSingleton($singleton);
    }

    protected function addAlias(Definition $definition, string|array $aliases): void
    {
        $aliases = is_string($aliases) ? [$aliases] : $aliases;
        foreach ($aliases as $alias) {
            $this->container->setAlias($definition->getId(), $alias);
        }
    }

    protected function setCallMethods(Definition $definition, array $methods): void
    {
        foreach ($methods as $method => $arguments) {
            $definition->addMethodCall($method, $arguments);
        }
    }

    protected function autowire(Definition $definition, bool $autowire): void
    {
        if ($autowire) {
            $definition->autowire();
        }
    }
}
