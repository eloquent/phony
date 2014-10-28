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
use Phake;
use PHPUnit_Framework_MockObject_Generator;

class MockGeneratorEvent extends AthleticEvent
{
    protected function setUp()
    {
        $this->phpunit = new PHPUnit_Framework_MockObject_Generator();
    }

    /**
     * @iterations 10
     */
    public function phony()
    {
        Phony::mock('Icecave\Isolator\Isolator');
    }

    /**
     * @iterations 10
     */
    public function phake()
    {
        Phake::mock('Icecave\Isolator\Isolator');
    }

    /**
     * @iterations 10
     */
    public function phpunit()
    {
        $this->phpunit->getMock('Icecave\Isolator\Isolator');
    }
}
