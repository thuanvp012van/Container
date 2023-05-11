<?php

namespace Penguin\Component\Container;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Autowire
{
    public function __construct(protected string $serviceId) {}

    public function __toString(): string
    {
        return $this->serviceId;
    }
}