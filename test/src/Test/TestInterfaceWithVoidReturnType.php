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

interface TestInterfaceWithVoidReturnType
{
    public static function staticMethod(): void;

    public static function __callStatic($name, array $arguments): void;

    public function method(): void;

    public function __call($name, array $arguments): void;
}
