<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy;

use Eloquent\Phony\Call\Factory\CallFactory;
use Eloquent\Phony\Spy\Factory\GeneratorSpyFactory;
use Eloquent\Phony\Spy\Factory\TraversableSpyFactory;
use Eloquent\Phony\Test\TestCallFactory;
use PHPUnit_Framework_TestCase;

class SpyWithGeneratorsTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->callback = 'implode';
        $this->label = 'label';
        $this->useGeneratorSpies = false;
        $this->useTraversableSpies = false;
        $this->callFactory = new TestCallFactory();
        $this->callEventFactory = $this->callFactory->eventFactory();
        $this->generatorSpyFactory = new GeneratorSpyFactory($this->callEventFactory);
        $this->traversableSpyFactory = new TraversableSpyFactory($this->callEventFactory);
        $this->subject = new Spy(
            $this->callback,
            $this->label,
            $this->useGeneratorSpies,
            $this->useTraversableSpies,
            $this->callFactory,
            $this->generatorSpyFactory,
            $this->traversableSpyFactory
        );

        $this->callA = $this->callFactory->create();
        $this->callB = $this->callFactory->create();
        $this->calls = array($this->callA, $this->callB);

        $this->callFactory->reset();
    }

    public function testInvokeWithWithGeneratorSpy()
    {
        $this->callback = function () {
            foreach (func_get_args() as $argument) {
                yield strtoupper($argument);
            }
        };
        $generator = call_user_func($this->callback);
        $spy = new Spy(
            $this->callback,
            true,
            true,
            null,
            $this->callFactory,
            $this->generatorSpyFactory,
            $this->traversableSpyFactory
        );
        foreach ($spy->invoke('a', 'b') as $value) {
        }
        foreach ($spy->invoke('c') as $value) {
        }
        $this->callFactory->reset();
        $expected = array(
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, array('a', 'b')),
                $this->callEventFactory->createGenerated($generator),
                array(
                    $this->callEventFactory->createProduced(0, 'A'),
                    $this->callEventFactory->createReceived(),
                    $this->callEventFactory->createProduced(1, 'B'),
                    $this->callEventFactory->createReceived(),
                ),
                $this->callEventFactory->createReturned()
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, array('c')),
                $this->callEventFactory->createGenerated($generator),
                array(
                    $this->callEventFactory->createProduced(0, 'C'),
                    $this->callEventFactory->createReceived(),
                ),
                $this->callEventFactory->createReturned()
            ),
        );

        $this->assertEquals($expected, $spy->recordedCalls());
    }
}
