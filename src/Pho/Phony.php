<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Pho;

use Eloquent\Phony\Facade\AbstractFacade;
use Eloquent\Phony\Facade\FacadeDriverInterface;
use Eloquent\Phony\Integration\Pho\PhoFacadeDriver;

/**
 * A facade for Phony usage under Pho.
 */
class Phony extends AbstractFacade
{
    /**
     * Get the facade driver.
     *
     * @internal
     *
     * @return FacadeDriverInterface The facade driver.
     */
    protected static function driver()
    {
        return PhoFacadeDriver::instance();
    }
}
