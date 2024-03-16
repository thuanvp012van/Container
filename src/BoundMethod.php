<?php

namespace Penguin\Component\Container;

use Penguin\Component\Container\Inject;
use Closure;

class BoundMethod
{
    public static function call(Closure|array $callback, array $parameters = []): mixed
    {
        if (is_array($callback)) {
            $concrete = $callback[0];
            $method = $callback[1];
            if (is_string($concrete)) {
                $container = Container::getInstance();
                if ($container->has($concrete)) {
                    $concrete = $container->get($concrete);
                    $callback[0] = $concrete;
                } else {
                    $concrete = new $concrete(...static::getObjectDependencies($concrete));
                    InjectProperties::handle($concrete);
                }
            }
            $parameters = static::getMethodDependencies($callback, $parameters);
            return $concrete->$method(...$parameters);
        }

        $parameters = static::getMethodDependencies($callback, $parameters);
        return $callback(...$parameters);
    }

    protected static function getMethodDependencies(Closure|array $callback, array $parameters = []): array
    {
        if ($callback instanceof Closure) {
            $reflection = new \ReflectionFunction($callback);
        } else {
            $reflection = new \ReflectionMethod($callback[0], $callback[1]);
        }

        return static::getMethodParameters($reflection, $parameters);
    }

    protected static function getObjectDependencies(string $class): array
    {
        $reflection = new \ReflectionClass($class);
        $construct = '__construct';
        if ($reflection->hasMethod($construct)) {
            $reflectionMethod = $reflection->getMethod($construct);
            return static::getMethodParameters($reflectionMethod);
        }
        return [];
    }

    protected static function getMethodParameters(\ReflectionMethod|\ReflectionFunction $reflectionMethod, array $parameters = []): array
    {
        $container = Container::getInstance();
        $params = $reflectionMethod->getParameters();
        foreach ($params as $key => $param) {
            if (!empty($attributes = $param->getAttributes(Inject::class))) {
                $paramType = (string)$attributes[0]->newInstance();
            } else {
                $paramType = (string)$param->getType();
            }
            if ($container->has($paramType)) {
                $params[$key] = $container->get($paramType);
            } else {
                unset($params[$key]);
            }
        }

        return array_values(array_merge($params, $parameters));
    }
}
