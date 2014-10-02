<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

use Eloquent\Phony\Integration\Phpunit\Phony;

class FunctionalTest extends PHPUnit_Framework_TestCase
{
    public function testSpy()
    {
        $spy = Phony::spy();
        $spy('a', 'b', 'c');
        $spy(111);

        $spy->assertCalledWith('a', 'b', 'c');
        $spy->assertCalledWith('a', 'b');
        $spy->assertCalledWith('a');
        $spy->assertCalledWith();
        $spy->assertCalledWith(111);
        $spy->assertCalledWith($this->identicalTo('a'), $this->anything());
        $spy->callAt(0)->assertCalledWith('a', 'b', 'c');
        $spy->callAt(1)->assertCalledWith(111);
    }
}
