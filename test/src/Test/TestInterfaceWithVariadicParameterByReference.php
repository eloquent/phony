<?php

namespace Eloquent\Phony\Test;

interface TestInterfaceWithVariadicParameterByReference
{
    public function method(&...$arguments);
}
