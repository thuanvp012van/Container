<?php

namespace Penguin\Component\Container;

/**
 * Tag represents a service tag.
 *
 * @author Nguyễn Hoàng Thắng Thuận <thuanvp012van@gmail.com>
 */
class Tag
{
    public function __construct(protected string $tag) {}

    public function __toString(): string
    {
        return $this->tag;
    }
}
