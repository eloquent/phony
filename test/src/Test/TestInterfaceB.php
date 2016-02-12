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

interface TestInterfaceB extends TestInterfaceA
{
    public static function testClassBStaticMethodA();

    public function testClassBMethodA();

    public function testClassBMethodB(&$first, &$second);
}
