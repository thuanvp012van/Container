<?php

namespace Penguin\Component\Container;

/**
 * Reference represents a service reference.
 *
 * @author Nguyễn Hoàng Thắng Thuận <thuanvp012van@gmail.com>
 */
class Reference
{
    public function __construct(protected string $id) {}

    public function __toString(): string
    {
        return $this->id;
    }
}
