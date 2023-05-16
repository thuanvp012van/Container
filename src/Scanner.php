<?php

namespace Penguin\Component\Container;

use ReflectionClass;
use ReflectionMethod;

/**
 * This file to scan the methods in the service.
 *
 * @author Nguyễn Hoàng Thắng Thuận <thuanvp012van@gmail.com>
 */
class Scanner
{
    protected ReflectionClass $reflection;

    public function __construct(string $class)
    {
        $this->reflection = new ReflectionClass($class);
    }

    public function getArguments(string $method = '__construct'): array
    {
        $params = $this->reflection->getMethod($method)->getParameters();
        $container = Container::getInstance();
        foreach ($params as &$param) {
            $attibutes = $param->getAttributes(Autowire::class);
            if (empty($attibutes)) {
                $abstract = $param->getType()->getName();
                $definitions = $container->getDefinitions();
                foreach ($definitions as $id => $definition) {
                    if ($abstract === $definition->getAbStract()) {
                        $param = new Reference($id);
                        break;
                    }
                }
            } else {
                $serviceId = (string) $attibutes[0]->newInstance();
                $param = new Reference($serviceId);
            }
        }

        unset($param);
        return $params;
    }

    public function getMethods(): array
    {
        $methods = $this->reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        $calls = [];
        foreach ($methods as $method) {
            $methodName = $method->getName();
            if (strpos($methodName, '__') !== 0) {
                $calls[$methodName] = $this->getArguments($methodName);
            }
        }

        return $calls;
    }
}
