<?php

namespace Eloquent\Phony\Test;

use Eloquent\Phony\Facade\AbstractFacade;

/**
 * A facade for Phony integration tests.
 */
class Phony extends AbstractFacade
{
    public static function reset()
    {
        self::driver()->reset();
    }

    protected static function driver()
    {
        return TestFacadeDriver::instance();
    }
}
