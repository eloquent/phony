<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Test;

class TestClassD
{
    private function __construct()
    {
        $this->constructorArguments = func_get_args();
    }

    public $constructorArguments;
}
