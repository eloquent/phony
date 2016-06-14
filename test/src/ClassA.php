<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

class ClassA
{
    public static function staticMethodA()
    {
    }

    public static function __callStatic($name, array $arguments)
    {
    }

    public function methodA()
    {
    }

    public function __call($name, array $arguments)
    {
    }
}
