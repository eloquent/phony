<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

interface TestInterfaceD
{
    public function __call($name, array $arguments);
}
