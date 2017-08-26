<?php

declare(strict_types=1);

namespace Eloquent\Phony;

use Eloquent\Phony\Facade\AbstractFacade;
use Eloquent\Phony\Facade\FacadeDriver;

/**
 * A facade for standalone Phony usage.
 */
class Phony extends AbstractFacade
{
    /**
     * Get the facade driver.
     *
     * @return FacadeDriver The facade driver.
     */
    protected static function driver(): FacadeDriver
    {
        return FacadeDriver::instance();
    }
}
