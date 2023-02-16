<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

class TestClassWithConstructorProperties
{
    public function __construct(
        public int $publicConstructor,
        protected int $protectedConstructor,
        private int $privateConstructor
    ) { }

    public function __get(string $name)
    {
        return $this->$name;
    }
}
