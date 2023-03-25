<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test\Php82;

readonly class TestClassReadonlySubclass extends TestClassReadonly
{
    public bool $propertyC;

    public function __construct()
    {
        parent::__construct();

        $this->propertyC = true;
    }
}
