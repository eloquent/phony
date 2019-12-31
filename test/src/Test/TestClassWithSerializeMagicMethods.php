<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

class TestClassWithSerializeMagicMethods
{
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    public function __serialize(): array
    {
        return $this->values;
    }

    public function __unserialize(array $values)
    {
        $this->values = $values;
    }

    public $values;
}
