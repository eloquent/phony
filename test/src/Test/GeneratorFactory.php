<?php

namespace Eloquent\Phony\Test;

abstract class GeneratorFactory
{
    public static function createEmpty()
    {
        return (function () { return; yield null; })();
    }
}
