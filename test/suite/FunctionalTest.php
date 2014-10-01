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
        $spy('argumentA', 'argumentB', 'argumentC');
        $spy(111);

        $spy->assertCalledWith('argumentA', 'argumentB', 'argumentC');
        $spy->assertCalledWith('argumentA', 'argumentB');
        $spy->assertCalledWith('argumentA');
        $spy->assertCalledWith();
        $spy->assertCalledWith(111);
        $spy->assertCalledWith($this->identicalTo('argumentA'), $this->anything());
        $spy->callAt(0)->assertCalledWith('argumentA', 'argumentB', 'argumentC');
        $spy->callAt(1)->assertCalledWith(111);
    }
}
