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

interface TestInterfaceWithSelfReturnType
{
    public static function staticMethod(): self;

    public static function __callStatic($name, array $arguments): self;

    public function method(): self;

    public function __call($name, array $arguments): self;
}
