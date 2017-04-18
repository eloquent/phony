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
    protected static function driver()
    {
        return FacadeDriver::instance();
    }

    public function untested1()
    {
        sprintf('This line is untested.');
    }

    public function untested2()
    {
        sprintf('This line is untested.');
    }

    public function untested3()
    {
        sprintf('This line is untested.');
    }

    public function untested4()
    {
        sprintf('This line is untested.');
    }

    public function untested5()
    {
        sprintf('This line is untested.');
    }

    public function untested6()
    {
        sprintf('This line is untested.');
    }
}
