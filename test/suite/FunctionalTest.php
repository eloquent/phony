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
    public function testTypicalCalledWith()
    {
        $spy = Phony::spy();
        $spy('argumentA', 'argumentB', 'argumentC');
        $spy(111);

        $this->assertTrue($spy->calledWith('argumentA', 'argumentB', 'argumentC'));
        $this->assertTrue($spy->calledWith('argumentA', 'argumentB'));
        $this->assertTrue($spy->calledWith('argumentA'));
        $this->assertTrue($spy->calledWith());
        $this->assertTrue($spy->calledWith(111));
        $this->assertTrue($spy->calledWith($this->identicalTo('argumentA'), $this->anything()));
    }

    public function testCallAssertCalledWith()
    {
        $spy = Phony::spy();
        $spy('argumentA', 'argumentB', 'argumentC');

        $spy->callAt(0)->assertCalledWith('argumentA', 'argumentB', 'argumentC');
    }
}
