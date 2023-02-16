<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

class TestClassWithReadonlyProperties
{
    public readonly int $public;
    protected readonly int $protected;
    private readonly int $private;

    public function __construct(
        public readonly int $publicConstructor,
        protected readonly int $protectedConstructor,
        private readonly int $privateConstructor
    ) {
        $this->public = 1;
        $this->protected = 2;
        $this->private = 3;
    }

    public function __get(string $name)
    {
        return $this->$name;
    }
}
