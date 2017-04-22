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

interface TestInterfaceWithNullableBuiltinTypes
{
    public function staticMethodA(? string $string, ? int $object): ? bool;

    public function staticMethodB(): ? int;

    public function methodA(? string $string, ? int $object): ? bool;

    public function methodB(): ? int;

    public function __call($name, array $arguments): ? int;

    public static function __callStatic($name, array $arguments): ? int;
}
