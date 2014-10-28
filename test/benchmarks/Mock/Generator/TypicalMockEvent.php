<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
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

class TypicalMockEvent extends AthleticEvent
{
    protected function setUp()
    {
        $this->className = 'Eloquent\Phony\Test\TestClassA';
        class_exists($this->className);

        $this->phpunit = new PHPUnit_Framework_MockObject_Generator();
        $this->prophecy = new Prophet();

        SimpleTest::getContext()->setTest(new SimpleTestCase());
    }

    /**
     * @iterations 100
     */
    public function phpunit()
    {
        $this->phpunit->getMock($this->className);
    }

    /**
     * @iterations 100
     */
    public function simpletest()
    {
        Mock::generate($this->className, 'SimpleTestTypicalMock');
        new \SimpleTestTypicalMock();
    }

    /**
     * @iterations 100
     */
    public function phake()
    {
        Phake::partialMock($this->className);
    }

    /**
     * @iterations 100
     */
    public function phony()
    {
        Phony::mock($this->className);
    }

    /**
     * @iterations 100
     */
    public function mockery()
    {
        Mockery::mock($this->className);
    }

    /**
     * @iterations 100
     */
    public function prophecy()
    {
        $this->prophecy->prophesize($this->className)->reveal();
    }
}
