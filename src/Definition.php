<?php

namespace Penguin\Component\Container;

/**
 * Definition represents a service definition.
 *
 * @author Nguyễn Hoàng Thắng Thuận <thuanvp012van@gmail.com>
 */
class Definition
{
    protected bool $singleton = true;

    protected array $tags = [];

    protected array $arguments = [];

    protected ?string $abstract;

    protected bool $autowire = false;

    public function __construct(protected string $id, protected string $class) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function setClass(string $class): static
    {
        $this->class = $class;
        return $this;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function setAbstract(string $abstract): static
    {
        $this->abstract = $abstract;
        return $this;
    }

    public function getAbStract(): ?string
    {
        return $this->abstract;
    }

    public function hasAbstract(): bool
    {
        return isset($this->abstract);
    }

    public function addArgument(mixed $argument): static
    {
        $this->arguments[] = $argument;
        return $this;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function singleton(bool $singleton = true): static
    {
        $this->singleton = $singleton;
        return $this;
    }

    public function isSingleton(): bool
    {
        return $this->singleton;
    }

    public function autowire(bool $autowire = true): static
    {
        $this->autowire = $autowire;
        return $this;
    }

    public function isAutowirte(): bool
    {
        return $this->autowire;
    }

    public function setTags(string|array $tags): static
    {
        $this->tags = is_string($tags) ? [$tags] : $tags;
        return $this;
    }
    
    public function addTag(string $tag): static
    {
        $this->tags[] = $tag;
        return $this;
    }

    public function removeTag(string $tag): static
    {
        $index = array_search($tag, $this->tags);
        unset($this->tags[$index]);
        return $this;
    }

    public function getTags(): array
    {
        return $this->tags;
    }
}
