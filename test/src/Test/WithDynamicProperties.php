<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

trait WithDynamicProperties
{
    private array $properties = [];

    public function __set($name, $value): void
    {
        $this->properties[$name] = $value;
    }

    public function __get($name): mixed
    {
        return $this->properties[$name];
    }
}
