<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2017 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

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
