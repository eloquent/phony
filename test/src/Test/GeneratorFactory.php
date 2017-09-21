<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

abstract class GeneratorFactory
{
    public static function createEmpty()
    {
        return (function () { return; yield null; })();
    }
}
