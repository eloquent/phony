<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony;

use Eloquent\Phony\Facade\AbstractFacade;
use Eloquent\Phony\Facade\FacadeDriver;
use Eloquent\Phony\Facade\FacadeDriverInterface;

/**
 * A facade for standalone Phony usage.
 *
 * @api
 */
class Phony extends AbstractFacade
{
    /**
     * Get the facade driver.
     *
     * @return FacadeDriverInterface The facade driver.
     */
    protected static function driver()
    {
        return FacadeDriver::instance();
    }

    public function untested1()
    {
        sprintf('This line is untested.');
    }
}
