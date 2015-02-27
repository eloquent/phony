<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Generator;

use Athletic\AthleticEvent;
use Eloquent\Phony\Phpunit\Phony;
use Mock;
use Mockery;
use Phake;
use PHPUnit_Framework_MockObject_Generator;
use Prophecy\Prophet;
use SimpleTest;
use SimpleTestCase;

class IsolatorMockEvent extends AthleticEvent
{
    protected function setUp()
    {
        $this->className = 'Icecave\Isolator\Isolator';
        class_exists($this->className);

        $this->phpunit = new PHPUnit_Framework_MockObject_Generator();
        $this->prophecy = new Prophet();

        SimpleTest::getContext()->setTest(new SimpleTestCase());
    }

    /**
     * @iterations 10
     */
    public function simpletest()
    {
        Mock::generate($this->className, 'SimpleTestIsolatorMock');
        new \SimpleTestIsolatorMock();
    }

    /**
     * @iterations 10
     */
    public function phony()
    {
        Phony::mock($this->className);
    }

    /**
     * @iterations 10
     */
    public function phake()
    {
        Phake::partialMock($this->className);
    }

    /**
     * @iterations 10
     */
    public function phpunit()
    {
        $this->phpunit->getMock($this->className);
    }

    /**
     * @iterations 10
     */
    public function mockery()
    {
        Mockery::mock($this->className);
    }

    /**
     * @iterations 10
     */
    public function prophecy()
    {
        $this->prophecy->prophesize($this->className)->reveal();
    }
}
