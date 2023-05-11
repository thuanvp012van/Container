<?php

namespace Penguin\Component\Container\Loader;

use Penguin\Component\Container\Definition;

/**
 * PhpFileLoader loads service definitions from an array PHP file.
 *
 * @author Nguyễn Hoàng Thắng Thuận <thuanvp012van@gmail.com>
 */
class PhpFileLoader extends BaseLoader
{
    protected const SERVICE_KEYWORDS = [
        'abstract' => 'setAbstract',
        'arguments' => 'addArguments',
        'alias' => 'addAlias',
        'singleton' => 'singleton',
        'tags' => 'addTags',
    ];

    public function load(mixed $resource): void
    {
        $services = require $resource;
        foreach ($services as $id => $keywords) {
            $definition = $this->container->register($id, $keywords['class']);
            foreach ($keywords as $key => $value) {
                if (isset(self::SERVICE_KEYWORDS[$key])) {
                    $this->{self::SERVICE_KEYWORDS[$key]}($definition, $value);
                }
            }
        }
    }

    protected function setAbstract(Definition $definition, string $abstract): void
    {
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
        $definition->singleton($singleton);
    }

    protected function addAlias(Definition $definition, string|array $aliases): void
    {
        $aliases = is_string($aliases) ? [$aliases] : $aliases;
        foreach ($aliases as $alias) {
            $this->container->setAlias($definition->getId(), $alias);
        }
    }
}