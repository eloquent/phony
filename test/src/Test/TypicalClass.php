<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Test;

use stdClass;

class TypicalClass
{
    const CONSTANT = 'yes';

    public function methodA()
    {
    }

    public function methodB($parameterA, $parameterB = null)
    {
    }

    public function methodC(stdClass $parameterA)
    {
    }

    public function methodD(&$parameterA = self::CONSTANT)
    {
    }

    public function methodE($parameterA = TypicalClass::CONSTANT)
    {
    }

    public function methodF(array $parameterA, callable $parameterB)
    {
    }
}
