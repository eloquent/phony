<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

use Eloquent\Phony\Phpunit\Phony;

class PhonyTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->proxy = Phony::mock('Eloquent\Phony\Test\TestClassA');
        $this->mock = $this->proxy->mock();
    }

    public function testShouldRecordPassingMockAssertions()
    {
        $this->mock->testClassAMethodA('a', 'b');

        $this->proxy->testClassAMethodA->calledWith($this->identicalTo('a'), 'b');
    }

    public function testShouldRecordFailingMockAssertions()
    {
        $this->proxy->testClassAMethodA->calledWith('a', 'b');
    }
}
