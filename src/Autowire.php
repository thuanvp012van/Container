<?php

namespace Penguin\Component\Container;

use Attribute;

/**
 * Attribute to tell a parameter how to be autowired.
 * 
 * @author Nguyễn Hoàng Thắng Thuận <thuanvp012van@gmail.com>
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class Autowire
{
    public function __construct(protected string $serviceId) {}

    public function __toString(): string
    {
        return $this->serviceId;
    }
}