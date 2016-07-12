<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy;

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Call\CallFactory;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Test\TestCallFactory;
use PHPUnit_Framework_TestCase;

class SpyWithGeneratorsTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->callback = 'implode';
        $this->label = 'label';
        $this->callFactory = new TestCallFactory();
        $this->invoker = new Invoker();
        $this->callEventFactory = $this->callFactory->eventFactory();
        $this->generatorSpyFactory = new GeneratorSpyFactory($this->callEventFactory, FeatureDetector::instance());
        $this->iterableSpyFactory = new IterableSpyFactory($this->callEventFactory);
        $this->subject = new SpyData(
            $this->callback,
            $this->label,
            $this->callFactory,
            $this->invoker,
            $this->generatorSpyFactory,
            $this->iterableSpyFactory
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
        $spy = new SpyData(
            $this->callback,
            null,
            $this->callFactory,
            $this->invoker,
            $this->generatorSpyFactory,
            $this->iterableSpyFactory
        );
        foreach ($spy->invoke('a', 'b') as $value) {
        }
        foreach ($spy->invoke('c') as $value) {
        }
        $this->callFactory->reset();
        $expected = array(
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, Arguments::create('a', 'b')),
                $this->callEventFactory->createReturned($generator),
                array(
                    $this->callEventFactory->createUsed(),
                    $this->callEventFactory->createProduced(0, 'A'),
                    $this->callEventFactory->createReceived(null),
                    $this->callEventFactory->createProduced(1, 'B'),
                    $this->callEventFactory->createReceived(null),
                ),
                $this->callEventFactory->createReturned(null)
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, Arguments::create('c')),
                $this->callEventFactory->createReturned($generator),
                array(
                    $this->callEventFactory->createUsed(),
                    $this->callEventFactory->createProduced(0, 'C'),
                    $this->callEventFactory->createReceived(null),
                ),
                $this->callEventFactory->createReturned(null)
            ),
        );

        $this->assertEquals($expected, $spy->allCalls());
    }
}
