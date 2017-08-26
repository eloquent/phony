<?php

namespace Eloquent\Phony\Test;

interface TestInterfaceD
{
    public function __call($name, array $arguments);
}
