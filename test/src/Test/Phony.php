<?php

namespace Eloquent\Phony\Test;

use Eloquent\Phony\Facade\FacadeTrait;

/**
 * A facade for Phony integration tests.
 */
class Phony
{
    use FacadeTrait;

    public static function reset()
    {
        self::driver()->reset();
    }

    protected static function driver()
    {
        return TestFacadeDriver::instance();
    }
}
