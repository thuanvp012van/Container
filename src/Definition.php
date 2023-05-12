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

    protected array $calls = [];

    protected ?string $abstract;

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

    public function addMethodCall(string $method, array $arguments = []): static
    {
        $this->calls[$method] = $arguments;
        return $this;
    }

    public function getMethodCall(string $method): array|null
    {
        return $this->hasMethodCall($method) ? [$method => $this->calls[$method]] : null;
    }

    public function removeMethodCall(string $method): static
    {
        unset($this->calls[$method]);
        return $this;
    }

    public function hasMethodCall(string $method): bool
    {
        return array_key_exists($method, $this->calls);
    }

    public function getMethodCalls(): array
    {
        return $this->calls;
    }

    public function setSingleton(bool $singleton = true): static
    {
        $this->singleton = $singleton;
        return $this;
    }

    public function isSingleton(): bool
    {
        return $this->singleton;
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

    public function clearTags(): static
    {
        $this->tags = [];
        return $this;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function autowire(): static
    {
        $scanner = new Scanner($this->getClass());
        $this->arguments = $scanner->getArguments();
        return $this;
    }
}
