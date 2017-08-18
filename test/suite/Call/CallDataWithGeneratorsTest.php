<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2017 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call;

use Eloquent\Phony\Call\Exception\UndefinedResponseException;
use Eloquent\Phony\Test\GeneratorFactory;
use Eloquent\Phony\Test\TestCallFactory;
use Exception;
use PHPUnit\Framework\TestCase;

class CallDataWithGeneratorsTest extends TestCase
{
    protected function setUp()
    {
        $this->index = 111;
        $this->callFactory = new TestCallFactory();
        $this->eventFactory = $this->callFactory->eventFactory();
        $this->callback = 'implode';
        $this->arguments = new Arguments(['a', 'b']);
        $this->calledEvent = $this->eventFactory->createCalled($this->callback, $this->arguments);
        $this->subject = new CallData($this->index, $this->calledEvent);

        $this->events = [$this->calledEvent];

        $this->generator = GeneratorFactory::createEmpty();
        $this->generatedEvent = $this->eventFactory->createReturned($this->generator);

        $this->returnValue = 'ab';
        $this->returnedEvent = $this->eventFactory->createReturned($this->returnValue);

        $this->exception = new Exception();
        $this->threwEvent = $this->eventFactory->createThrew($this->exception);
    }

    public function testResponseMethodsWithGeneratorReturn()
    {
        $this->subject->setResponseEvent($this->generatedEvent);
        $this->subject->setEndEvent($this->returnedEvent);

        $this->assertTrue($this->subject->isIterable());
        $this->assertTrue($this->subject->isGenerator());
        $this->assertSame($this->returnValue, $this->subject->generatorReturnValue());
        $this->assertSame([null, $this->returnValue], $this->subject->generatorResponse());
    }

    public function testResponseMethodsWithGeneratorException()
    {
        $this->subject->setResponseEvent($this->generatedEvent);
        $this->subject->setEndEvent($this->threwEvent);

        $this->assertTrue($this->subject->isIterable());
        $this->assertTrue($this->subject->isGenerator());
        $this->assertSame([$this->exception, null], $this->subject->generatorResponse());
    }

    public function testGeneratorResponseFailureWithNonGeneratorReturn()
    {
        $this->subject->setResponseEvent($this->eventFactory->createReturned([]));
        $this->subject->setEndEvent($this->eventFactory->createConsumed());

        $this->assertTrue($this->subject->isIterable());
        $this->assertFalse($this->subject->isGenerator());

        $this->expectException(UndefinedResponseException::class);
        $this->subject->generatorResponse();
    }

    public function testGeneratorResponseFailureWithoutEndEvent()
    {
        $this->subject->setResponseEvent($this->generatedEvent);

        $this->assertTrue($this->subject->isIterable());
        $this->assertTrue($this->subject->isGenerator());

        $this->expectException(UndefinedResponseException::class);
        $this->subject->generatorResponse();
    }

    public function testGeneratorResponseFailureWithoutResponseEvent()
    {
        $this->assertFalse($this->subject->isIterable());
        $this->assertFalse($this->subject->isGenerator());

        $this->expectException(UndefinedResponseException::class);
        $this->subject->generatorResponse();
    }

    public function testGeneratorReturnValueFailureWithGeneratorException()
    {
        $this->subject->setResponseEvent($this->generatedEvent);
        $this->subject->setEndEvent($this->threwEvent);

        $this->expectException(UndefinedResponseException::class);
        $this->subject->generatorReturnValue();
    }

    public function testGeneratorExceptionFailureWithGeneratorReturn()
    {
        $this->subject->setResponseEvent($this->generatedEvent);
        $this->subject->setEndEvent($this->returnedEvent);

        $this->expectException(UndefinedResponseException::class);
        $this->subject->generatorException();
    }
}
