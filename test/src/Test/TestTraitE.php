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

trait TestTraitE
{
    public function __construct($first, $second, $third = null)
    {
        $this->constructorArguments = func_get_args();
    }
}
