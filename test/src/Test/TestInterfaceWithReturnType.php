<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Test;

interface TestInterfaceWithReturnType
{
    public function classType() : \stdClass;
    public function scalarType() : int;

    public function __call($name, array $arguments) : string;
    public static function __callStatic($name, array $arguments) : string;
}
