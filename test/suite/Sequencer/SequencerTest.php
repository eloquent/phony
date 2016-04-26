<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Sequencer;

use PHPUnit_Framework_TestCase;
use ReflectionClass;

class SequencerTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->subject = new Sequencer();
    }

    public function testConstructor()
    {
        $this->assertSame(-1, $this->subject->get());
    }

    public function testSet()
    {
        $this->subject->set(111);

        $this->assertSame(111, $this->subject->get());
    }

    public function testReset()
    {
        $this->subject->set(111);
        $this->subject->reset();

        $this->assertSame(-1, $this->subject->get());
    }

    public function testNext()
    {
        $this->assertSame(0, $this->subject->next());
        $this->assertSame(1, $this->subject->next());
        $this->assertSame(2, $this->subject->next());
    }

    public function testSequence()
    {
        $reflector = new ReflectionClass('Eloquent\Phony\Sequencer\Sequencer');
        $property = $reflector->getProperty('instances');
        $property->setAccessible(true);
        $instances = $property->getValue(null);
        $property->setValue(null, null);
        $instanceA = Sequencer::sequence('a');
        $instanceB = Sequencer::sequence('b');

        $this->assertInstanceOf('Eloquent\Phony\Sequencer\Sequencer', $instanceA);
        $this->assertInstanceOf('Eloquent\Phony\Sequencer\Sequencer', $instanceB);
        $this->assertSame($instanceA, Sequencer::sequence('a'));
        $this->assertSame($instanceB, Sequencer::sequence('b'));
        $this->assertNotSame($instanceA, $instanceB);

        $property->setValue(null, $instances);
    }
}
