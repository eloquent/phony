<?php

declare(strict_types=1);

namespace Eloquent\Phony\Spy;

use AllowDynamicProperties;
use Eloquent\Phony\Assertion\Exception\AssertionException;
use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Call\Exception\UndefinedCallException;
use Eloquent\Phony\Event\EventSequence;
use Eloquent\Phony\Event\Exception\UndefinedEventException;
use Eloquent\Phony\Test\Facade\FacadeContainer;
use Eloquent\Phony\Test\GeneratorFactory;
use Eloquent\Phony\Test\TestClassA;
use Eloquent\Phony\Test\TestClassWithVariadicNamedArguments;
use Eloquent\Phony\Verification\Cardinality;
use Error;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionFunction;
use ReflectionMethod;
use RuntimeException;

#[AllowDynamicProperties]
class SpyVerifierTest extends TestCase
{
    protected function setUp(): void
    {
        $this->container = FacadeContainer::withTestCallFactory();
        $this->callFactory = $this->container->callFactory;
        $this->eventFactory = $this->container->eventFactory;
        $this->container->differenceEngine->setUseColor(false);
        $this->container->assertionRenderer->setUseColor(false);

        $this->callVerifierFactory = $this->container->callVerifierFactory;
        $this->generatorVerifierFactory = $this->container->generatorVerifierFactory;
        $this->iterableVerifierFactory = $this->container->iterableVerifierFactory;
        $this->matcherFactory = $this->container->matcherFactory;

        $this->callback = 'implode';
        $this->parameters = (new ReflectionFunction('implode'))->getParameters();
        $this->label = 'label';
        $this->spy = new SpyData(
            $this->callback,
            $this->parameters,
            $this->label,
            $this->container->callFactory,
            $this->container->invoker,
            $this->container->generatorSpyFactory,
            $this->container->iterableSpyFactory
        );

        $this->subject = new SpyVerifier(
            $this->spy,
            $this->container->matcherFactory,
            $this->container->matcherVerifier,
            $this->container->generatorVerifierFactory,
            $this->container->iterableVerifierFactory,
            $this->container->callVerifierFactory,
            $this->container->assertionRecorder,
            $this->container->assertionRenderer
        );

        $this->generatorVerifierFactory->setCallVerifierFactory($this->callVerifierFactory);
        $this->iterableVerifierFactory->setCallVerifierFactory($this->callVerifierFactory);

        $this->callEventFactory = $this->callFactory->eventFactory();

        $this->returnValueA = 'x';
        $this->returnValueB = 'y';
        $this->exceptionA = new RuntimeException('You done goofed.');
        $this->exceptionB = new RuntimeException('Consequences will never be the same.');
        $this->thisValueA = new TestClassA();
        $this->thisValueB = new TestClassA();
        $this->arguments = Arguments::create('a', 'b', 'c');
        $this->otherMatcher = $this->matcherFactory->adapt('d');
        $this->callA = $this->callFactory->create(
            $this->callEventFactory->createCalled([$this->thisValueA, 'testClassAMethodA'], [], $this->arguments),
            ($responseEvent = $this->callEventFactory->createReturned($this->returnValueA)),
            null,
            $responseEvent
        );
        $this->callAResponse = $this->callA->responseEvent();
        $this->callB = $this->callFactory->create(
            $this->callEventFactory->createCalled([$this->thisValueB, 'testClassAMethodA']),
            ($responseEvent = $this->callEventFactory->createReturned($this->returnValueB)),
            null,
            $responseEvent
        );
        $this->callBResponse = $this->callB->responseEvent();
        $this->callC = $this->callFactory->create(
            $this->callEventFactory->createCalled([$this->thisValueA, 'testClassAMethodA'], [], $this->arguments),
            ($responseEvent = $this->callEventFactory->createThrew($this->exceptionA)),
            null,
            $responseEvent
        );
        $this->callCResponse = $this->callC->responseEvent();
        $this->callD = $this->callFactory->create(
            $this->callEventFactory->createCalled('implode'),
            ($responseEvent = $this->callEventFactory->createThrew($this->exceptionB)),
            null,
            $responseEvent
        );
        $this->callDResponse = $this->callD->responseEvent();
        $this->callE = $this->callFactory->create($this->callEventFactory->createCalled('implode'));
        $this->calls = [$this->callA, $this->callB, $this->callC, $this->callD, $this->callE];
        $this->wrappedCallA = $this->callVerifierFactory->fromCall($this->callA);
        $this->wrappedCallB = $this->callVerifierFactory->fromCall($this->callB);
        $this->wrappedCallC = $this->callVerifierFactory->fromCall($this->callC);
        $this->wrappedCallD = $this->callVerifierFactory->fromCall($this->callD);
        $this->wrappedCallE = $this->callVerifierFactory->fromCall($this->callE);
        $this->wrappedCalls = [
            $this->wrappedCallA,
            $this->wrappedCallB,
            $this->wrappedCallC,
            $this->wrappedCallD,
            $this->wrappedCallE,
        ];

        $this->iteratorCalledEvent = $this->callEventFactory->createCalled();
        $this->returnedIterableEvent =
            $this->callEventFactory->createReturned(['m' => 'n', 'p' => 'q', 'r' => 's', 'u' => 'v']);
        $this->iteratorEventA = $this->callEventFactory->createProduced('m', 'n');
        $this->iteratorEventC = $this->callEventFactory->createProduced('p', 'q');
        $this->iteratorEventE = $this->callEventFactory->createProduced('r', 's');
        $this->iteratorEventG = $this->callEventFactory->createProduced('u', 'v');
        $this->iteratorEvents = [
            $this->iteratorEventA,
            $this->iteratorEventC,
            $this->iteratorEventE,
            $this->iteratorEventG,
        ];
        $this->iterableEndEvent = $this->callEventFactory->createConsumed();
        $this->iteratorCall = $this->callFactory->create(
            $this->iteratorCalledEvent,
            $this->returnedIterableEvent,
            $this->iteratorEvents,
            $this->iterableEndEvent
        );
        $this->iteratorCallWithNoEnd = $this->callFactory->create(
            $this->iteratorCalledEvent,
            $this->returnedIterableEvent,
            $this->iteratorEvents
        );

        $this->callFactory->reset();

        $this->receivedExceptionA = new RuntimeException('Consequences will never be the same.');
        $this->receivedExceptionB = new RuntimeException('Because I backtraced it.');
        $this->generatorCalledEvent = $this->eventFactory->createCalled();
        $this->generatedEvent = $this->eventFactory->createReturned(GeneratorFactory::createEmpty());
        $this->generatorEventA = $this->eventFactory->createProduced('m', 'n');
        $this->generatorEventB = $this->eventFactory->createReceived('o');
        $this->generatorEventC = $this->eventFactory->createProduced('p', 'q');
        $this->generatorEventD = $this->eventFactory->createReceivedException($this->receivedExceptionA);
        $this->generatorEventE = $this->eventFactory->createProduced('r', 's');
        $this->generatorEventF = $this->eventFactory->createReceived('t');
        $this->generatorEventG = $this->eventFactory->createProduced('u', 'v');
        $this->generatorEventH = $this->eventFactory->createReceivedException($this->receivedExceptionB);
        $this->generatorEvents = [
            $this->generatorEventA,
            $this->generatorEventB,
            $this->generatorEventC,
            $this->generatorEventD,
            $this->generatorEventE,
            $this->generatorEventF,
            $this->generatorEventG,
            $this->generatorEventH,
        ];
        $this->generatorEndEvent = $this->eventFactory->createReturned(null);
        $this->generatorCall = $this->callFactory->create(
            $this->generatorCalledEvent,
            $this->generatedEvent,
            $this->generatorEvents,
            $this->generatorEndEvent
        );

        $this->callFactory->reset();
    }

    public function testConstructor()
    {
        $this->assertSame($this->spy, $this->subject->spy());
        $this->assertEquals(new Cardinality(1, -1), $this->subject->cardinality());
    }

    public function testProxyMethods()
    {
        $this->assertFalse($this->subject->isAnonymous());
        $this->assertSame($this->callback, $this->subject->callback());
        $this->assertSame($this->parameters, $this->subject->parameters());
        $this->assertSame($this->label, $this->subject->label());
    }

    public function testSetLabel()
    {
        $this->assertSame($this->subject, $this->subject->setLabel(''));
        $this->assertSame('', $this->subject->label());

        $this->subject->setLabel($this->label);

        $this->assertSame($this->label, $this->subject->label());
    }

    public function testSetUseGeneratorSpies()
    {
        $this->assertSame($this->subject, $this->subject->setUseGeneratorSpies(true));
        $this->assertTrue($this->subject->useGeneratorSpies());
    }

    public function testSetUseIterableSpies()
    {
        $this->assertSame($this->subject, $this->subject->setUseIterableSpies(true));
        $this->assertTrue($this->subject->useIterableSpies());
    }

    public function testSetCalls()
    {
        $this->subject->setCalls($this->calls);

        $this->assertSame($this->calls, $this->subject->spy()->allCalls());
    }

    public function testAddCall()
    {
        $this->subject->addCall($this->callA);

        $this->assertSame([$this->callA], $this->subject->spy()->allCalls());

        $this->subject->addCall($this->callB);

        $this->assertSame([$this->callA, $this->callB], $this->subject->spy()->allCalls());
    }

    public function testHasEvents()
    {
        $this->assertFalse($this->subject->hasEvents());

        $this->subject->addCall($this->callA);

        $this->assertTrue($this->subject->hasEvents());
    }

    public function testHasCalls()
    {
        $this->assertFalse($this->subject->hasCalls());

        $this->subject->addCall($this->callA);

        $this->assertTrue($this->subject->hasCalls());
    }

    public function testEventCount()
    {
        $this->assertSame(0, $this->subject->eventCount());

        $this->subject->addCall($this->callA);

        $this->assertSame(1, $this->subject->eventCount());
    }

    public function testCallCount()
    {
        $this->assertSame(0, $this->subject->callCount());
        $this->assertCount(0, $this->subject);

        $this->subject->addCall($this->callA);

        $this->assertSame(1, $this->subject->callCount());
        $this->assertCount(1, $this->subject);
    }

    public function testAllEvents()
    {
        $this->assertSame([], $this->subject->allEvents());

        $this->subject->addCall($this->callA);

        $this->assertSame([$this->callA], $this->subject->allEvents());
    }

    public function testAllCalls()
    {
        $this->assertSame([], $this->subject->allCalls());

        $this->subject->setCalls($this->calls);

        $this->assertEquals($this->wrappedCalls, $this->subject->allCalls());
        $this->assertEquals($this->wrappedCalls, iterator_to_array($this->subject));
    }

    public function testFirstEvent()
    {
        $this->subject->setCalls($this->calls);

        $this->assertSame($this->callA, $this->subject->firstEvent());
    }

    public function testFirstEventFailureUndefined()
    {
        $this->subject->setCalls([]);

        $this->expectException(UndefinedEventException::class);
        $this->subject->firstEvent();
    }

    public function testLastEvent()
    {
        $this->subject->setCalls($this->calls);

        $this->assertSame($this->callE, $this->subject->lastEvent());
    }

    public function testLastEventFailureUndefined()
    {
        $this->subject->setCalls([]);

        $this->expectException(UndefinedEventException::class);
        $this->subject->lastEvent();
    }

    public function testEventAt()
    {
        $this->subject->addCall($this->callA);

        $this->assertSame($this->callA, $this->subject->eventAt());
        $this->assertSame($this->callA, $this->subject->eventAt(0));
        $this->assertSame($this->callA, $this->subject->eventAt(-1));
    }

    public function testEventAtFailure()
    {
        $this->expectException(UndefinedEventException::class);
        $this->subject->eventAt();
    }

    public function testFirstCall()
    {
        $this->subject->setCalls($this->calls);

        $this->assertEquals($this->wrappedCallA, $this->subject->firstCall());
    }

    public function testFirstCallFailureUndefined()
    {
        $this->subject->setCalls([]);

        $this->expectException(UndefinedCallException::class);
        $this->subject->firstCall();
    }

    public function testLastCall()
    {
        $this->subject->setCalls($this->calls);

        $this->assertEquals($this->wrappedCallE, $this->subject->lastCall());
    }

    public function testLastCallFailureUndefined()
    {
        $this->subject->setCalls([]);

        $this->expectException(UndefinedCallException::class);
        $this->subject->lastCall();
    }

    public function testCallAt()
    {
        $this->subject->setCalls($this->calls);

        $this->assertEquals($this->wrappedCallA, $this->subject->callAt(0));
        $this->assertEquals($this->wrappedCallB, $this->subject->callAt(1));
    }

    public function testCallAtFailureUndefined()
    {
        $this->expectException(UndefinedCallException::class);
        $this->subject->callAt(0);
    }

    public function testInvokeMethods()
    {
        $verifier = $this->subject;
        $spy = $verifier->spy();
        $verifier->invokeWith([['a']]);
        $verifier->invoke(['b', 'c']);
        $verifier(['d']);
        $this->callFactory->reset();
        $expected = [
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, $this->parameters, Arguments::create(['a'])),
                ($responseEvent = $this->callEventFactory->createReturned('a')),
                null,
                $responseEvent
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, $this->parameters, Arguments::create(['b', 'c'])),
                ($responseEvent = $this->callEventFactory->createReturned('bc')),
                null,
                $responseEvent
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, $this->parameters, Arguments::create(['d'])),
                ($responseEvent = $this->callEventFactory->createReturned('d')),
                null,
                $responseEvent
            ),
        ];

        $this->assertEquals($expected, $this->spy->allCalls());
    }

    public function testInvokeMethodsWithNamedArguments()
    {
        $verifier = $this->subject;
        $spy = $verifier->spy();
        $verifier->invokeWith(['array' => ['1'], 'separator' => ',']);
        $verifier->invoke(array: ['2', '3'], separator: ',');
        $verifier(array: ['4'], separator: ',');
        $this->callFactory->reset();
        $expected = [
            $this->callFactory->create(
                $this->eventFactory
                    ->createCalled($spy, $this->parameters, Arguments::create(array: ['1'], separator: ',')),
                ($responseEvent = $this->eventFactory->createReturned('1')),
                null,
                $responseEvent
            ),
            $this->callFactory->create(
                $this->eventFactory
                    ->createCalled($spy, $this->parameters, Arguments::create(array: ['2', '3'], separator: ',')),
                ($responseEvent = $this->eventFactory->createReturned('2,3')),
                null,
                $responseEvent
            ),
            $this->callFactory->create(
                $this->eventFactory
                    ->createCalled($spy, $this->parameters, Arguments::create(array: ['4'], separator: ',')),
                ($responseEvent = $this->eventFactory->createReturned('4')),
                null,
                $responseEvent
            ),
        ];

        $this->assertEquals($expected, $spy->allCalls());
    }

    public function testInvokeMethodsWithoutSubject()
    {
        $spy = new SpyData(
            null,
            [],
            '111',
            $this->container->callFactory,
            $this->container->invoker,
            $this->container->generatorSpyFactory,
            $this->container->iterableSpyFactory
        );
        $verifier = new SpyVerifier(
            $spy,
            $this->container->matcherFactory,
            $this->container->matcherVerifier,
            $this->container->generatorVerifierFactory,
            $this->container->iterableVerifierFactory,
            $this->container->callVerifierFactory,
            $this->container->assertionRecorder,
            $this->container->assertionRenderer
        );
        $verifier->invokeWith(['a']);
        $verifier->invoke('b', 'c');
        $verifier('d');
        $this->callFactory->reset();
        $expected = [
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, [], Arguments::create('a')),
                ($responseEvent = $this->callEventFactory->createReturned(null)),
                null,
                $responseEvent
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, [], Arguments::create('b', 'c')),
                ($responseEvent = $this->callEventFactory->createReturned(null)),
                null,
                $responseEvent
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, [], Arguments::create('d')),
                ($responseEvent = $this->callEventFactory->createReturned(null)),
                null,
                $responseEvent
            ),
        ];

        $this->assertEquals($expected, $spy->allCalls());
    }

    public function testInvokeWithExceptionThrown()
    {
        $exceptions = [new Exception(), new Exception(), new Exception()];
        $index = 0;
        $callback = function () use (&$exceptions, &$index) {
            $exception = $exceptions[$index++];
            throw $exception;
        };
        $spy = new SpyData(
            $callback,
            [],
            '111',
            $this->container->callFactory,
            $this->container->invoker,
            $this->container->generatorSpyFactory,
            $this->container->iterableSpyFactory
        );
        $verifier = new SpyVerifier(
            $spy,
            $this->container->matcherFactory,
            $this->container->matcherVerifier,
            $this->container->generatorVerifierFactory,
            $this->container->iterableVerifierFactory,
            $this->container->callVerifierFactory,
            $this->container->assertionRecorder,
            $this->container->assertionRenderer
        );
        try {
            $verifier->invokeWith(['a']);
        } catch (Exception $caughtException) {
        }
        try {
            $verifier->invoke('b', 'c');
        } catch (Exception $caughtException) {
        }
        try {
            $verifier('d');
        } catch (Exception $caughtException) {
        }
        $this->callFactory->reset();
        $expected = [
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, [], Arguments::create('a')),
                ($responseEvent = $this->callEventFactory->createThrew($exceptions[0])),
                null,
                $responseEvent
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, [], Arguments::create('b', 'c')),
                ($responseEvent = $this->callEventFactory->createThrew($exceptions[1])),
                null,
                $responseEvent
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, [], Arguments::create('d')),
                ($responseEvent = $this->callEventFactory->createThrew($exceptions[2])),
                null,
                $responseEvent
            ),
        ];

        $this->assertEquals($expected, $spy->allCalls());
    }

    public function testInvokeWithWithReferenceParameters()
    {
        $callback = function (&$argument) {
            $argument = 'x';
        };
        $spy = new SpyData(
            $callback,
            (new ReflectionFunction($callback))->getParameters(),
            '111',
            $this->container->callFactory,
            $this->container->invoker,
            $this->container->generatorSpyFactory,
            $this->container->iterableSpyFactory
        );
        $verifier = new SpyVerifier(
            $spy,
            $this->container->matcherFactory,
            $this->container->matcherVerifier,
            $this->container->generatorVerifierFactory,
            $this->container->iterableVerifierFactory,
            $this->container->callVerifierFactory,
            $this->container->assertionRecorder,
            $this->container->assertionRenderer
        );
        $value = null;
        $arguments = [&$value];
        $verifier->invokeWith($arguments);

        $this->assertSame('x', $value);
    }

    public function testStopRecording()
    {
        $callback = function () {
            return 'x';
        };
        $spy = new SpyData(
            $callback,
            [],
            '111',
            $this->container->callFactory,
            $this->container->invoker,
            $this->container->generatorSpyFactory,
            $this->container->iterableSpyFactory
        );
        $verifier = new SpyVerifier(
            $spy,
            $this->container->matcherFactory,
            $this->container->matcherVerifier,
            $this->container->generatorVerifierFactory,
            $this->container->iterableVerifierFactory,
            $this->container->callVerifierFactory,
            $this->container->assertionRecorder,
            $this->container->assertionRenderer
        );
        $verifier->stopRecording()->invokeWith();
        $this->callFactory->reset();

        $this->assertSame([], $spy->allCalls());
    }

    public function testStartRecording()
    {
        $callback = function () {
            return 'x';
        };
        $spy = new SpyData(
            $callback,
            [],
            '111',
            $this->container->callFactory,
            $this->container->invoker,
            $this->container->generatorSpyFactory,
            $this->container->iterableSpyFactory
        );
        $verifier = new SpyVerifier(
            $spy,
            $this->container->matcherFactory,
            $this->container->matcherVerifier,
            $this->container->generatorVerifierFactory,
            $this->container->iterableVerifierFactory,
            $this->container->callVerifierFactory,
            $this->container->assertionRecorder,
            $this->container->assertionRenderer
        );
        $verifier->stopRecording()->invoke('a');
        $verifier->startRecording()->invoke('b');
        $this->callFactory->reset();
        $expected = [
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, [], Arguments::create('b')),
                ($responseEvent = $this->callEventFactory->createReturned('x')),
                null,
                $responseEvent
            ),
        ];

        $this->assertEquals($expected, $spy->allCalls());
    }

    public function testCheckCalled()
    {
        $this->assertFalse((bool) $this->subject->checkCalled());

        $this->subject->setCalls($this->calls);

        $this->assertTrue((bool) $this->subject->checkCalled());
    }

    public function testCalled()
    {
        $this->subject->setCalls($this->calls);
        $expected = new EventSequence($this->calls, $this->callVerifierFactory);

        $this->assertEquals($expected, $this->subject->called());
    }

    public function testCalledFailure()
    {
        $this->expectException(AssertionException::class);
        $this->expectExceptionMessage('Never called.');
        $this->subject->called();
    }

    public function testCheckCalledOnce()
    {
        $this->assertFalse((bool) $this->subject->once()->checkCalled());

        $this->subject->addCall($this->callA);

        $this->assertTrue((bool) $this->subject->once()->checkCalled());

        $this->subject->addCall($this->callB);

        $this->assertFalse((bool) $this->subject->once()->checkCalled());
    }

    public function testCalledOnce()
    {
        $this->subject->addCall($this->callA);
        $expected = new EventSequence([$this->callA], $this->callVerifierFactory);

        $this->assertEquals($expected, $this->subject->once()->called());
    }

    public function testCalledOnceFailureWithNoCalls()
    {
        $this->expectException(AssertionException::class);
        $this->subject->once()->called();
    }

    public function testCalledOnceFailureWithMultipleCalls()
    {
        $this->subject->setCalls($this->calls);
        $this->expectException(AssertionException::class);
        $this->subject->once()->called();
    }

    public function testCheckCalledTimes()
    {
        $this->assertTrue((bool) $this->subject->times(0)->checkCalled());
        $this->assertFalse((bool) $this->subject->times(5)->checkCalled());

        $this->subject->setCalls($this->calls);

        $this->assertFalse((bool) $this->subject->times(0)->checkCalled());
        $this->assertTrue((bool) $this->subject->times(5)->checkCalled());
    }

    public function testCalledTimes()
    {
        $this->subject->setCalls($this->calls);
        $expected = new EventSequence($this->calls, $this->callVerifierFactory);

        $this->assertEquals($expected, $this->subject->times(5)->called());
    }

    public function testCalledTimesFailure()
    {
        $this->subject->setCalls($this->calls);
        $this->expectException(AssertionException::class);
        $this->subject->times(2)->called();
    }

    public function calledWithData()
    {
        //                               arguments             calledWith calledWithWildcard
        return [
            'Exact arguments'        => [['a', 'b', 'c'],      true,      true],
            'First arguments'        => [['a', 'b'],           false,     true],
            'Single argument'        => [['a'],                false,     true],
            'Last arguments'         => [['b', 'c'],           false,     false],
            'Last argument'          => [['c'],                false,     false],
            'Extra arguments'        => [['a', 'b', 'c', 'd'], false,     false],
            'First argument differs' => [['d', 'b', 'c'],      false,     false],
            'Last argument differs'  => [['a', 'b', 'd'],      false,     false],
            'Unused argument'        => [['d'],                false,     false],
        ];
    }

    /**
     * @dataProvider calledWithData
     */
    public function testCheckCalledWith(array $arguments, $calledWith, $calledWithWildcard)
    {
        $this->subject->setCalls($this->calls);

        $this->assertSame(
            $calledWith,
            (bool) call_user_func_array([$this->subject, 'checkCalledWith'], $arguments)
        );

        $arguments[] = $this->matcherFactory->wildcard();

        $this->assertSame(
            $calledWithWildcard,
            (bool) call_user_func_array([$this->subject, 'checkCalledWith'], $arguments)
        );
    }

    public function testCheckCalledWithWithWildcardOnly()
    {
        $this->subject->setCalls($this->calls);

        $this->assertTrue((bool) $this->subject->checkCalledWith($this->matcherFactory->wildcard()));
    }

    public function testCheckCalledWithWithWildcardOnlyWithNoCalls()
    {
        $this->assertFalse((bool) $this->subject->checkCalledWith($this->matcherFactory->wildcard()));
    }

    public function testCheckCalledWithWithNamedArguments()
    {
        $call = $this->callFactory->create(
            $this->callEventFactory->createCalled(
                'implode',
                (new ReflectionFunction('implode'))->getParameters(),
                new Arguments(['separator' => ',', 'array' => ['a', 'b']])
            ),
            ($responseEvent = $this->callEventFactory->createReturned('a,b')),
            null,
            $responseEvent
        );
        $this->subject->setCalls([$call]);

        $this->assertTrue((bool) $this->subject->checkCalledWith(',', ['a', 'b']));
        $this->assertTrue((bool) $this->subject->checkCalledWith(array: ['a', 'b'], separator: ','));
    }

    public function testCheckCalledWithWithPositionalArguments()
    {
        $call = $this->callFactory->create(
            $this->callEventFactory->createCalled(
                'implode',
                (new ReflectionFunction('implode'))->getParameters(),
                new Arguments([',', ['a', 'b']])
            ),
            ($responseEvent = $this->callEventFactory->createReturned('a,b')),
            null,
            $responseEvent
        );
        $this->subject->setCalls([$call]);

        $this->assertTrue((bool) $this->subject->checkCalledWith(',', ['a', 'b']));
        $this->assertTrue((bool) $this->subject->checkCalledWith(array: ['a', 'b'], separator: ','));
    }

    public function testCheckCalledWithWithPositionalVariadicArguments()
    {
        $subject = new SpyVerifier(
            new SpyData(
                [TestClassWithVariadicNamedArguments::class, 'setStaticArguments'],
                (new ReflectionMethod(TestClassWithVariadicNamedArguments::class, 'setStaticArguments'))
                    ->getParameters(),
                'label',
                $this->container->callFactory,
                $this->container->invoker,
                $this->container->generatorSpyFactory,
                $this->container->iterableSpyFactory
            ),
            $this->container->matcherFactory,
            $this->container->matcherVerifier,
            $this->container->generatorVerifierFactory,
            $this->container->iterableVerifierFactory,
            $this->container->callVerifierFactory,
            $this->container->assertionRecorder,
            $this->container->assertionRenderer
        );
        $call = $this->callFactory->create(
            $this->callEventFactory->createCalled(
                [TestClassWithVariadicNamedArguments::class, 'setStaticArguments'],
                (new ReflectionMethod(TestClassWithVariadicNamedArguments::class, 'setStaticArguments'))
                    ->getParameters(),
                new Arguments([1, 2, 'x' => 3, 'y' => 4]),
            ),
            ($responseEvent = $this->callEventFactory->createReturned(null)),
            null,
            $responseEvent
        );
        $subject->setCalls([$call]);

        $this->assertTrue((bool) $subject->checkCalledWith(1, 2, x: 3, y: 4));
        $this->assertTrue((bool) $subject->checkCalledWith(a: 1, b: 2, x: 3, y: 4));
    }

    public function testCheckCalledWithWithNamedVariadicArguments()
    {
        $subject = new SpyVerifier(
            new SpyData(
                [TestClassWithVariadicNamedArguments::class, 'setStaticArguments'],
                (new ReflectionMethod(TestClassWithVariadicNamedArguments::class, 'setStaticArguments'))
                    ->getParameters(),
                'label',
                $this->container->callFactory,
                $this->container->invoker,
                $this->container->generatorSpyFactory,
                $this->container->iterableSpyFactory
            ),
            $this->container->matcherFactory,
            $this->container->matcherVerifier,
            $this->container->generatorVerifierFactory,
            $this->container->iterableVerifierFactory,
            $this->container->callVerifierFactory,
            $this->container->assertionRecorder,
            $this->container->assertionRenderer
        );
        $call = $this->callFactory->create(
            $this->callEventFactory->createCalled(
                [TestClassWithVariadicNamedArguments::class, 'setStaticArguments'],
                (new ReflectionMethod(TestClassWithVariadicNamedArguments::class, 'setStaticArguments'))
                    ->getParameters(),
                new Arguments(['a' => 1, 'b' => 2, 'x' => 3, 'y' => 4]),
            ),
            ($responseEvent = $this->callEventFactory->createReturned(null)),
            null,
            $responseEvent
        );
        $subject->setCalls([$call]);

        $this->assertTrue((bool) $subject->checkCalledWith(1, 2, x: 3, y: 4));
        $this->assertTrue((bool) $subject->checkCalledWith(a: 1, b: 2, x: 3, y: 4));
    }

    public function testCalledWith()
    {
        $this->subject->setCalls($this->calls);
        $expected = new EventSequence([$this->callA, $this->callC], $this->callVerifierFactory);

        $this->assertEquals($expected, $this->subject->calledWith('a', 'b', 'c'));
        $this->assertEquals($expected, $this->subject->calledWith('a', 'b', $this->matcherFactory->wildcard()));
        $this->assertEquals($expected, $this->subject->calledWith('a', $this->matcherFactory->wildcard()));
        $this->assertEquals(
            new EventSequence($this->calls, $this->callVerifierFactory),
            $this->subject->calledWith($this->matcherFactory->wildcard())
        );
    }

    public function testCalledWithWithNamedArguments()
    {
        $call = $this->callFactory->create(
            $this->callEventFactory->createCalled(
                'implode',
                (new ReflectionFunction('implode'))->getParameters(),
                new Arguments(['separator' => ',', 'array' => ['a', 'b']])
            ),
            ($responseEvent = $this->callEventFactory->createReturned('a,b')),
            null,
            $responseEvent
        );
        $this->subject->setCalls([$call]);
        $assertionResult = new EventSequence([$call], $this->callVerifierFactory);

        $this->assertEquals($assertionResult, $this->subject->calledWith(',', ['a', 'b']));
        $this->assertEquals($assertionResult, $this->subject->calledWith(array: ['a', 'b'], separator: ','));
    }

    public function testCalledWithWithPositionalArguments()
    {
        $call = $this->callFactory->create(
            $this->callEventFactory->createCalled(
                'implode',
                (new ReflectionFunction('implode'))->getParameters(),
                new Arguments([',', ['a', 'b']])
            ),
            ($responseEvent = $this->callEventFactory->createReturned('a,b')),
            null,
            $responseEvent
        );
        $this->subject->setCalls([$call]);
        $assertionResult = new EventSequence([$call], $this->callVerifierFactory);

        $this->assertEquals($assertionResult, $this->subject->calledWith(',', ['a', 'b']));
        $this->assertEquals($assertionResult, $this->subject->calledWith(array: ['a', 'b'], separator: ','));
    }

    public function testCalledWithWithPositionalVariadicArguments()
    {
        $subject = new SpyVerifier(
            new SpyData(
                [TestClassWithVariadicNamedArguments::class, 'setStaticArguments'],
                (new ReflectionMethod(TestClassWithVariadicNamedArguments::class, 'setStaticArguments'))
                    ->getParameters(),
                'label',
                $this->container->callFactory,
                $this->container->invoker,
                $this->container->generatorSpyFactory,
                $this->container->iterableSpyFactory
            ),
            $this->container->matcherFactory,
            $this->container->matcherVerifier,
            $this->container->generatorVerifierFactory,
            $this->container->iterableVerifierFactory,
            $this->container->callVerifierFactory,
            $this->container->assertionRecorder,
            $this->container->assertionRenderer
        );
        $call = $this->callFactory->create(
            $this->callEventFactory->createCalled(
                [TestClassWithVariadicNamedArguments::class, 'setStaticArguments'],
                (new ReflectionMethod(TestClassWithVariadicNamedArguments::class, 'setStaticArguments'))
                    ->getParameters(),
                new Arguments([1, 2, 'x' => 3, 'y' => 4]),
            ),
            ($responseEvent = $this->callEventFactory->createReturned(null)),
            null,
            $responseEvent
        );
        $subject->setCalls([$call]);
        $assertionResult = new EventSequence([$call], $this->callVerifierFactory);

        $this->assertEquals($assertionResult, $subject->calledWith(1, 2, x: 3, y: 4));
        $this->assertEquals($assertionResult, $subject->calledWith(a: 1, b: 2, x: 3, y: 4));
    }

    public function testCalledWithWithNamedVariadicArguments()
    {
        $subject = new SpyVerifier(
            new SpyData(
                [TestClassWithVariadicNamedArguments::class, 'setStaticArguments'],
                (new ReflectionMethod(TestClassWithVariadicNamedArguments::class, 'setStaticArguments'))
                    ->getParameters(),
                'label',
                $this->container->callFactory,
                $this->container->invoker,
                $this->container->generatorSpyFactory,
                $this->container->iterableSpyFactory
            ),
            $this->container->matcherFactory,
            $this->container->matcherVerifier,
            $this->container->generatorVerifierFactory,
            $this->container->iterableVerifierFactory,
            $this->container->callVerifierFactory,
            $this->container->assertionRecorder,
            $this->container->assertionRenderer
        );
        $call = $this->callFactory->create(
            $this->callEventFactory->createCalled(
                [TestClassWithVariadicNamedArguments::class, 'setStaticArguments'],
                (new ReflectionMethod(TestClassWithVariadicNamedArguments::class, 'setStaticArguments'))
                    ->getParameters(),
                new Arguments(['a' => 1, 'b' => 2, 'x' => 3, 'y' => 4]),
            ),
            ($responseEvent = $this->callEventFactory->createReturned(null)),
            null,
            $responseEvent
        );
        $subject->setCalls([$call]);
        $assertionResult = new EventSequence([$call], $this->callVerifierFactory);

        $this->assertEquals($assertionResult, $subject->calledWith(1, 2, x: 3, y: 4));
        $this->assertEquals($assertionResult, $subject->calledWith(a: 1, b: 2, x: 3, y: 4));
    }

    public function testCalledWithFailure()
    {
        $this->subject->setCalls($this->calls);

        $this->expectException(AssertionException::class);
        $this->subject->calledWith('b', 'c');
    }

    public function testCalledWithFailureWithNoMatchers()
    {
        $this->subject->setCalls([$this->callA]);

        $this->expectException(AssertionException::class);
        $this->subject->calledWith();
    }

    public function testCalledWithFailureMissingArguments()
    {
        $this->subject->setCalls($this->calls);

        $this->expectException(AssertionException::class);
        $this->subject->calledWith('a', 'b', 'c', 'd', 'e');
    }

    public function testCalledWithFailureWithNoCalls()
    {
        $this->expectException(AssertionException::class);
        $this->subject->calledWith('b', 'c');
    }

    public function testCalledWithFailureWithNoCallsAndNoMatchers()
    {
        $this->expectException(AssertionException::class);
        $this->subject->calledWith();
    }

    public function testCheckCalledOnceWith()
    {
        $this->assertFalse((bool) $this->subject->once()->checkCalledWith());

        $this->subject->setCalls([$this->callA, $this->callB]);

        $this->assertTrue((bool) $this->subject->once()->checkCalledWith('a', 'b', 'c'));
        $this->assertFalse((bool) $this->subject->once()->checkCalledWith($this->matcherFactory->wildcard()));
        $this->assertFalse((bool) $this->subject->once()->checkCalledWith($this->matcherFactory->wildcard()));
    }

    public function testCalledOnceWith()
    {
        $this->subject->setCalls([$this->callA, $this->callB]);
        $expected = new EventSequence([$this->callA], $this->callVerifierFactory);

        $this->assertEquals($expected, $this->subject->once()->calledWith('a', 'b', 'c'));
    }

    public function testCalledOnceWithFailure()
    {
        $this->subject->setCalls($this->calls);

        $this->expectException(AssertionException::class);
        $this->subject->once()->calledWith('a', 'b', 'c');
    }

    public function testCalledOnceWithFailureWithNoCalls()
    {
        $this->expectException(AssertionException::class);
        $this->subject->once()->calledWith('a', 'b', 'c');
    }

    public function testCheckCalledTimesWith()
    {
        $this->subject->setCalls($this->calls);

        $this->assertTrue((bool) $this->subject->times(2)->checkCalledWith('a', 'b', 'c'));
        $this->assertTrue((bool) $this->subject->times(2)->checkCalledWith('a', $this->matcherFactory->wildcard()));
        $this->assertTrue((bool) $this->subject->times(5)->checkCalledWith($this->matcherFactory->wildcard()));
        $this->assertFalse((bool) $this->subject->times(1)->checkCalledWith('a', 'b', 'c'));
        $this->assertFalse((bool) $this->subject->times(1)->checkCalledWith('a'));
        $this->assertFalse((bool) $this->subject->times(1)->checkCalledWith($this->matcherFactory->wildcard()));
        $this->assertFalse((bool) $this->subject->times(1)->checkCalledWith($this->matcherFactory->wildcard()));
    }

    public function testCalledTimesWith()
    {
        $this->subject->setCalls($this->calls);
        $expected = new EventSequence([$this->callA, $this->callC], $this->callVerifierFactory);

        $this->assertEquals($expected, $this->subject->times(2)->calledWith('a', 'b', 'c'));
        $this->assertEquals($expected, $this->subject->times(2)->calledWith('a', $this->matcherFactory->wildcard()));

        $expected = new EventSequence($this->calls, $this->callVerifierFactory);

        $this->assertEquals($expected, $this->subject->times(5)->calledWith($this->matcherFactory->wildcard()));
        $this->assertEquals($expected, $this->subject->times(5)->calledWith($this->matcherFactory->wildcard()));
    }

    public function testCalledTimesWithFailure()
    {
        $this->subject->setCalls($this->calls);

        $this->expectException(AssertionException::class);
        $this->subject->times(5)->calledWith('a', 'b', 'c');
    }

    public function testCalledTimesWithFailureWithNoMatchers()
    {
        $this->subject->setCalls($this->calls);

        $this->expectException(AssertionException::class);
        $this->subject->times(2)->calledWith();
    }

    public function testCalledTimesWithFailureWithNoCalls()
    {
        $this->expectException(AssertionException::class);
        $this->subject->times(5)->calledWith('a', 'b', 'c');
    }

    /**
     * @dataProvider calledWithData
     */
    public function testCheckAlwaysCalledWith(array $arguments, $calledWith, $calledWithWildcard)
    {
        $this->subject->setCalls([$this->callA, $this->callA]);

        $this->assertSame(
            $calledWith,
            (bool) call_user_func_array([$this->subject->always(), 'checkCalledWith'], $arguments)
        );

        $arguments[] = $this->matcherFactory->wildcard();

        $this->assertSame(
            $calledWithWildcard,
            (bool) call_user_func_array([$this->subject->always(), 'checkCalledWith'], $arguments)
        );
    }

    /**
     * @dataProvider calledWithData
     */
    public function testCheckAlwaysCalledWithWithDifferingCalls(array $arguments, $calledWith, $calledWithWildcard)
    {
        $this->subject->setCalls([$this->callA, $this->callB]);
        $arguments[] = $this->matcherFactory->wildcard();

        $this->assertFalse(
            (bool) call_user_func_array([$this->subject->always(), 'checkCalledWith'], $arguments)
        );
    }

    public function testCheckAlwaysCalledWithWithWildcardOnly()
    {
        $this->subject->setCalls($this->calls);

        $this->assertTrue((bool) $this->subject->always()->checkCalledWith($this->matcherFactory->wildcard()));
    }

    public function testCheckAlwaysCalledWithWithWildcardOnlyWithNoCalls()
    {
        $this->assertFalse((bool) $this->subject->always()->checkCalledWith($this->matcherFactory->wildcard()));
    }

    public function testAlwaysCalledWith()
    {
        $this->subject->setCalls([$this->callA, $this->callA]);
        $expected = new EventSequence([$this->callA, $this->callA], $this->callVerifierFactory);

        $this->assertEquals($expected, $this->subject->always()->calledWith('a', 'b', 'c'));
        $this->assertEquals(
            $expected,
            $this->subject->always()->calledWith('a', 'b', $this->matcherFactory->wildcard())
        );
        $this->assertEquals($expected, $this->subject->always()->calledWith('a', $this->matcherFactory->wildcard()));
        $this->assertEquals($expected, $this->subject->always()->calledWith($this->matcherFactory->wildcard()));
    }

    public function testAlwaysCalledWithFailure()
    {
        $this->subject->setCalls($this->calls);

        $this->expectException(AssertionException::class);
        $this->subject->always()->calledWith('a', 'b', 'c');
    }

    public function testAlwaysCalledWithFailureWithNoMatchers()
    {
        $this->subject->setCalls($this->calls);

        $this->expectException(AssertionException::class);
        $this->subject->always()->calledWith();
    }

    public function testAlwaysCalledWithFailureWithNoCalls()
    {
        $this->expectException(AssertionException::class);
        $this->subject->always()->calledWith('a', 'b', 'c');
    }

    /**
     * @dataProvider calledWithData
     */
    public function testCheckNeverCalledWith(array $arguments, $calledWith, $calledWithWildcard)
    {
        $this->subject->setCalls($this->calls);

        $this->assertSame(
            !$calledWith,
            (bool) call_user_func_array([$this->subject->never(), 'checkCalledWith'], $arguments)
        );
    }

    public function testCheckNeverCalledWithWithEmptyArguments()
    {
        $this->subject->setCalls($this->calls);

        $this->assertFalse((bool) $this->subject->never()->checkCalledWith());
    }

    public function testCheckNeverCalledWithWithNoCalls()
    {
        $this->assertTrue((bool) $this->subject->never()->checkCalledWith());
    }

    public function testNeverCalledWith()
    {
        $expected = new EventSequence([], $this->callVerifierFactory);

        $this->assertEquals($expected, $this->subject->never()->calledWith());

        $this->subject->setCalls($this->calls);

        $this->assertEquals($expected, $this->subject->never()->calledWith('b', 'c'));
        $this->assertEquals($expected, $this->subject->never()->calledWith('c'));
        $this->assertEquals($expected, $this->subject->never()->calledWith('a', 'b', 'c', 'd'));
        $this->assertEquals($expected, $this->subject->never()->calledWith('d', 'b', 'c'));
        $this->assertEquals($expected, $this->subject->never()->calledWith('a', 'b', 'd'));
        $this->assertEquals($expected, $this->subject->never()->calledWith('d'));
        $this->assertEquals($expected, $this->subject->never()->calledWith($this->otherMatcher));
    }

    public function testNeverCalledWithFailure()
    {
        $this->subject->setCalls($this->calls);

        $this->expectException(AssertionException::class);
        $this->subject->never()->calledWith('a', 'b', 'c');
    }

    public function testNeverCalledWithFailureWithNoMatchers()
    {
        $this->subject->setCalls($this->calls);

        $this->expectException(AssertionException::class);
        $this->subject->never()->calledWith();
    }

    public function testNeverCalledWithFailureWithWildcard()
    {
        $this->subject->setCalls($this->calls);

        $this->expectException(AssertionException::class);
        $this->subject->never()->calledWith('*');
    }

    public function testCheckResponded()
    {
        $this->assertFalse((bool) $this->subject->checkResponded());
        $this->assertTrue((bool) $this->subject->never()->checkResponded());

        $this->subject->addCall($this->callE);

        $this->assertFalse((bool) $this->subject->checkResponded());
        $this->assertTrue((bool) $this->subject->never()->checkResponded());

        $this->subject->setCalls($this->calls);

        $this->assertTrue((bool) $this->subject->checkResponded());
        $this->assertFalse((bool) $this->subject->never()->checkResponded());

        $this->subject->setCalls([$this->iteratorCall]);

        $this->assertTrue((bool) $this->subject->checkResponded());
    }

    public function testResponded()
    {
        $this->assertEquals(
            new EventSequence([], $this->callVerifierFactory),
            $this->subject->never()->responded()
        );

        $this->subject->setCalls($this->calls);

        $this->assertEquals(
            new EventSequence(
                [
                    $this->callA->responseEvent(),
                    $this->callB->responseEvent(),
                    $this->callC->responseEvent(),
                    $this->callD->responseEvent(),
                ],
                $this->callVerifierFactory
            ),
            $this->subject->responded()
        );

        $this->subject->setCalls([$this->iteratorCall]);

        $this->assertEquals(
            new EventSequence([$this->iteratorCall->responseEvent()], $this->callVerifierFactory),
            $this->subject->responded()
        );
    }

    public function testRespondedFailure()
    {
        $this->subject->setCalls([$this->callE, $this->callE]);
        $this->expectException(AssertionException::class);
        $this->subject->responded();
    }

    public function testRespondedFailureWithNoCalls()
    {
        $this->expectException(AssertionException::class);
        $this->subject->responded();
    }

    public function testCheckAlwaysResponded()
    {
        $this->assertFalse((bool) $this->subject->always()->checkResponded());

        $this->subject->setCalls($this->calls);

        $this->assertFalse((bool) $this->subject->always()->checkResponded());

        $this->subject->setCalls([$this->callA, $this->callB]);

        $this->assertTrue((bool) $this->subject->always()->checkResponded());
    }

    public function testAlwaysResponded()
    {
        $this->subject->setCalls([$this->callA, $this->callB]);
        $expected = new EventSequence(
            [$this->callA->responseEvent(), $this->callB->responseEvent()],
            $this->callVerifierFactory
        );

        $this->assertEquals($expected, $this->subject->always()->responded());
    }

    public function testAlwaysRespondedFailure()
    {
        $this->subject->setCalls($this->calls);
        $this->expectException(AssertionException::class);
        $this->subject->always()->responded();
    }

    public function testAlwaysRespondedFailureWithNoCalls()
    {
        $this->expectException(AssertionException::class);
        $this->subject->always()->responded();
    }

    public function testCheckCompleted()
    {
        $this->assertFalse((bool) $this->subject->checkCompleted());
        $this->assertTrue((bool) $this->subject->never()->checkCompleted());

        $this->subject->addCall($this->callE);

        $this->assertFalse((bool) $this->subject->checkCompleted());
        $this->assertTrue((bool) $this->subject->never()->checkCompleted());

        $this->subject->addCall($this->iteratorCallWithNoEnd);

        $this->assertFalse((bool) $this->subject->checkCompleted());
        $this->assertTrue((bool) $this->subject->never()->checkCompleted());

        $this->subject->setCalls($this->calls);

        $this->assertTrue((bool) $this->subject->checkCompleted());
        $this->assertFalse((bool) $this->subject->never()->checkCompleted());

        $this->subject->setCalls([$this->iteratorCall]);

        $this->assertTrue((bool) $this->subject->checkCompleted());
    }

    public function testCompleted()
    {
        $this->assertEquals(
            new EventSequence([], $this->callVerifierFactory),
            $this->subject->never()->completed()
        );

        $this->subject->setCalls($this->calls);

        $this->assertEquals(
            new EventSequence(
                [
                    $this->callA->endEvent(),
                    $this->callB->endEvent(),
                    $this->callC->endEvent(),
                    $this->callD->endEvent(),
                ],
                $this->callVerifierFactory
            ),
            $this->subject->completed()
        );

        $this->subject->setCalls([$this->iteratorCall]);

        $this->assertEquals(
            new EventSequence([$this->iteratorCall->endEvent()], $this->callVerifierFactory),
            $this->subject->completed()
        );
    }

    public function testCompletedFailure()
    {
        $this->subject->setCalls([$this->iteratorCallWithNoEnd, $this->iteratorCallWithNoEnd]);
        $this->expectException(AssertionException::class);
        $this->subject->completed();
    }

    public function testCompletedFailureWithNoCalls()
    {
        $this->expectException(AssertionException::class);
        $this->subject->completed();
    }

    public function testCheckAlwaysCompleted()
    {
        $this->assertFalse((bool) $this->subject->always()->checkCompleted());

        $this->subject->setCalls($this->calls);

        $this->assertFalse((bool) $this->subject->always()->checkCompleted());

        $this->subject->setCalls([$this->callA, $this->iteratorCall]);

        $this->assertTrue((bool) $this->subject->always()->checkCompleted());
    }

    public function testAlwaysCompleted()
    {
        $this->subject->setCalls([$this->callA, $this->iteratorCall]);
        $expected = new EventSequence(
            [$this->callA->endEvent(), $this->iteratorCall->endEvent()],
            $this->callVerifierFactory
        );

        $this->assertEquals($expected, $this->subject->always()->completed());
    }

    public function testAlwaysCompletedFailure()
    {
        $this->subject->setCalls($this->calls);
        $this->subject->addCall($this->iteratorCall);

        $this->expectException(AssertionException::class);
        $this->subject->always()->completed();
    }

    public function testAlwaysCompletedFailureWithNoCalls()
    {
        $this->expectException(AssertionException::class);
        $this->subject->always()->completed();
    }

    public function testCheckReturned()
    {
        $this->assertFalse((bool) $this->subject->checkReturned());
        $this->assertFalse((bool) $this->subject->checkReturned(null));
        $this->assertFalse((bool) $this->subject->checkReturned($this->returnValueA));
        $this->assertFalse((bool) $this->subject->checkReturned($this->returnValueB));
        $this->assertFalse(
            (bool) $this->subject->checkReturned($this->matcherFactory->equalTo($this->returnValueA))
        );
        $this->assertFalse((bool) $this->subject->checkReturned('z'));

        $this->subject->setCalls($this->calls);

        $this->assertTrue((bool) $this->subject->checkReturned());
        $this->assertFalse((bool) $this->subject->checkReturned(null));
        $this->assertTrue((bool) $this->subject->checkReturned($this->returnValueA));
        $this->assertTrue((bool) $this->subject->checkReturned($this->returnValueB));
        $this->assertTrue((bool) $this->subject->checkReturned($this->matcherFactory->equalTo($this->returnValueA)));
        $this->assertFalse((bool) $this->subject->checkReturned('z'));
    }

    public function testReturned()
    {
        $this->subject->setCalls($this->calls);

        $this->assertEquals(
            new EventSequence([$this->callAResponse, $this->callBResponse], $this->callVerifierFactory),
            $this->subject->returned()
        );
        $this->assertEquals(
            new EventSequence([$this->callAResponse], $this->callVerifierFactory),
            $this->subject->returned($this->returnValueA)
        );
        $this->assertEquals(
            new EventSequence([$this->callBResponse], $this->callVerifierFactory),
            $this->subject->returned($this->returnValueB)
        );
        $this->assertEquals(
            new EventSequence([$this->callAResponse], $this->callVerifierFactory),
            $this->subject->returned($this->matcherFactory->equalTo($this->returnValueA))
        );
    }

    public function testReturnedFailure()
    {
        $this->subject->setCalls($this->calls);
        $this->expectException(AssertionException::class);
        $this->subject->returned('z');
    }

    public function testReturnedFailureWithoutMatcher()
    {
        $this->subject->setCalls([$this->callC, $this->callD]);
        $this->expectException(AssertionException::class);
        $this->subject->returned();
    }

    public function testReturnedFailureWithNoCalls()
    {
        $this->expectException(AssertionException::class);
        $this->subject->returned($this->returnValueA);
    }

    public function testCheckAlwaysReturned()
    {
        $this->assertFalse((bool) $this->subject->always()->checkReturned());
        $this->assertFalse((bool) $this->subject->always()->checkReturned(null));
        $this->assertFalse((bool) $this->subject->always()->checkReturned($this->returnValueA));
        $this->assertFalse((bool) $this->subject->always()->checkReturned($this->returnValueB));
        $this->assertFalse(
            (bool) $this->subject->always()->checkReturned($this->matcherFactory->equalTo($this->returnValueA))
        );
        $this->assertFalse((bool) $this->subject->always()->checkReturned('z'));

        $this->subject->setCalls($this->calls);

        $this->assertFalse((bool) $this->subject->always()->checkReturned());
        $this->assertFalse((bool) $this->subject->always()->checkReturned(null));
        $this->assertFalse((bool) $this->subject->always()->checkReturned($this->returnValueA));
        $this->assertFalse((bool) $this->subject->always()->checkReturned($this->returnValueB));
        $this->assertFalse(
            (bool) $this->subject->always()->checkReturned($this->matcherFactory->equalTo($this->returnValueA))
        );
        $this->assertFalse((bool) $this->subject->always()->checkReturned('z'));

        $this->subject->setCalls([$this->callA, $this->callA]);

        $this->assertTrue((bool) $this->subject->always()->checkReturned());
        $this->assertTrue((bool) $this->subject->always()->checkReturned($this->returnValueA));
        $this->assertTrue(
            (bool) $this->subject->always()->checkReturned($this->matcherFactory->equalTo($this->returnValueA))
        );
        $this->assertFalse((bool) $this->subject->always()->checkReturned(null));
        $this->assertFalse((bool) $this->subject->always()->checkReturned($this->returnValueB));
        $this->assertFalse((bool) $this->subject->always()->checkReturned('y'));
    }

    public function testAlwaysReturned()
    {
        $this->subject->setCalls([$this->callA, $this->callA]);
        $expected = new EventSequence([$this->callAResponse, $this->callAResponse], $this->callVerifierFactory);

        $this->assertEquals($expected, $this->subject->always()->returned());
        $this->assertEquals($expected, $this->subject->always()->returned($this->returnValueA));
        $this->assertEquals(
            $expected,
            $this->subject->always()->returned($this->matcherFactory->equalTo($this->returnValueA))
        );
    }

    public function testAlwaysReturnedFailure()
    {
        $this->subject->setCalls($this->calls);
        $this->expectException(AssertionException::class);
        $this->subject->always()->returned($this->returnValueA);
    }

    public function testAlwaysReturnedFailureWithNoMatcher()
    {
        $this->subject->setCalls($this->calls);
        $this->expectException(AssertionException::class);
        $this->subject->always()->returned();
    }

    public function testAlwaysReturnedFailureWithNoCalls()
    {
        $this->expectException(AssertionException::class);
        $this->subject->always()->returned($this->returnValueA);
    }

    public function testCheckThrew()
    {
        $this->assertFalse((bool) $this->subject->checkThrew());
        $this->assertFalse((bool) $this->subject->checkThrew(Exception::class));
        $this->assertFalse((bool) $this->subject->checkThrew(RuntimeException::class));
        $this->assertFalse((bool) $this->subject->checkThrew($this->exceptionA));
        $this->assertFalse((bool) $this->subject->checkThrew($this->exceptionB));
        $this->assertFalse((bool) $this->subject->checkThrew($this->matcherFactory->equalTo($this->exceptionA)));
        $this->assertFalse((bool) $this->subject->checkThrew(InvalidArgumentException::class));
        $this->assertFalse((bool) $this->subject->checkThrew(new Exception()));
        $this->assertFalse((bool) $this->subject->checkThrew(new RuntimeException()));
        $this->assertFalse((bool) $this->subject->checkThrew($this->matcherFactory->equalTo(null)));

        $this->subject->setCalls($this->calls);

        $this->assertTrue((bool) $this->subject->checkThrew());
        $this->assertTrue((bool) $this->subject->checkThrew(Exception::class));
        $this->assertTrue((bool) $this->subject->checkThrew(RuntimeException::class));
        $this->assertTrue((bool) $this->subject->checkThrew($this->exceptionA));
        $this->assertTrue((bool) $this->subject->checkThrew($this->exceptionB));
        $this->assertTrue((bool) $this->subject->checkThrew($this->matcherFactory->equalTo($this->exceptionA)));
        $this->assertFalse((bool) $this->subject->checkThrew(InvalidArgumentException::class));
        $this->assertFalse((bool) $this->subject->checkThrew(new Exception()));
        $this->assertFalse((bool) $this->subject->checkThrew(new RuntimeException()));
        $this->assertFalse((bool) $this->subject->checkThrew($this->matcherFactory->equalTo(null)));
    }

    public function testCheckThrewFailureInvalidInput()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to match exceptions against 111.');
        $this->subject->checkThrew(111);
    }

    public function testCheckThrewFailureInvalidInputObject()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to match exceptions against #0{}.');
        $this->subject->checkThrew((object) []);
    }

    public function testThrew()
    {
        $this->subject->setCalls($this->calls);

        $this->assertEquals(
            new EventSequence([$this->callCResponse, $this->callDResponse], $this->callVerifierFactory),
            $this->subject->threw()
        );
        $this->assertEquals(
            new EventSequence([$this->callCResponse, $this->callDResponse], $this->callVerifierFactory),
            $this->subject->threw(Exception::class)
        );
        $this->assertEquals(
            new EventSequence([$this->callCResponse, $this->callDResponse], $this->callVerifierFactory),
            $this->subject->threw(RuntimeException::class)
        );
        $this->assertEquals(
            new EventSequence([$this->callCResponse], $this->callVerifierFactory),
            $this->subject->threw($this->exceptionA)
        );
        $this->assertEquals(
            new EventSequence([$this->callDResponse], $this->callVerifierFactory),
            $this->subject->threw($this->exceptionB)
        );
        $this->assertEquals(
            new EventSequence([$this->callCResponse], $this->callVerifierFactory),
            $this->subject->threw($this->matcherFactory->equalTo($this->exceptionA))
        );
    }

    public function testThrewWithEngineErrorException()
    {
        $this->exceptionA = new Error('You done goofed.');
        $this->exceptionB = new Error('Consequences will never be the same.');
        $this->callC = $this->callFactory->create(
            $this->callEventFactory->createCalled([$this->thisValueA, 'testClassAMethodA'], [], $this->arguments),
            ($responseEvent = $this->callEventFactory->createThrew($this->exceptionA)),
            null,
            $responseEvent
        );
        $this->callCResponse = $this->callC->responseEvent();
        $this->callD = $this->callFactory->create(
            $this->callEventFactory->createCalled('implode'),
            ($responseEvent = $this->callEventFactory->createThrew($this->exceptionB)),
            null,
            $responseEvent
        );
        $this->callDResponse = $this->callD->responseEvent();
        $this->calls = [$this->callA, $this->callB, $this->callC, $this->callD];
        $this->wrappedCallC = $this->callVerifierFactory->fromCall($this->callC);
        $this->wrappedCallD = $this->callVerifierFactory->fromCall($this->callD);
        $this->wrappedCalls = [$this->wrappedCallA, $this->wrappedCallB, $this->wrappedCallC, $this->wrappedCallD];
        $this->subject->setCalls($this->calls);

        $this->assertEquals(
            new EventSequence([$this->callCResponse, $this->callDResponse], $this->callVerifierFactory),
            $this->subject->threw()
        );
    }

    public function testThrewWithInstanceHandle()
    {
        $builder = $this->container->mockBuilderFactory->create(RuntimeException::class);
        $exception = $builder->get();
        $threwEvent = $this->callEventFactory->createThrew($exception);
        $call = $this->callFactory->create(
            $this->callEventFactory->createCalled([$this->thisValueA, 'testClassAMethodA'], [], $this->arguments),
            $threwEvent,
            null,
            $threwEvent
        );
        $this->subject->setCalls([$call]);
        $handle = $this->container->handleFactory->instanceHandle($exception);

        $this->assertTrue((bool) $this->subject->threw($handle));
        $this->assertTrue((bool) $this->subject->checkThrew($handle));
    }

    public function testThrewFailureExpectingAny()
    {
        $this->subject->setCalls([$this->callA, $this->callB]);
        $this->expectException(AssertionException::class);
        $this->subject->threw();
    }

    public function testThrewFailureExpectingAnyWithNoCalls()
    {
        $this->expectException(AssertionException::class);
        $this->subject->threw();
    }

    public function testThrewFailureExpectingType()
    {
        $this->subject->setCalls([$this->callC, $this->callD]);

        $this->expectException(AssertionException::class);
        $this->subject->threw(UndefinedCallException::class);
    }

    public function testThrewFailureExpectingTypeWithNoCalls()
    {
        $this->expectException(AssertionException::class);
        $this->subject->threw(UndefinedCallException::class);
    }

    public function testThrewFailureExpectingTypeWithNoExceptions()
    {
        $this->subject->setCalls([$this->callA, $this->callB]);

        $this->expectException(AssertionException::class);
        $this->subject->threw(UndefinedCallException::class);
    }

    public function testThrewFailureExpectingException()
    {
        $this->subject->setCalls([$this->callC, $this->callD]);

        $this->expectException(AssertionException::class);
        $this->subject->threw(new RuntimeException());
    }

    public function testThrewFailureExpectingExceptionWithNoCalls()
    {
        $this->expectException(AssertionException::class);
        $this->subject->threw(new RuntimeException());
    }

    public function testThrewFailureExpectingExceptionWithNoExceptions()
    {
        $this->subject->setCalls([$this->callA, $this->callB]);

        $this->expectException(AssertionException::class);
        $this->subject->threw(new RuntimeException());
    }

    public function testThrewFailureExpectingMatcher()
    {
        $this->subject->setCalls([$this->callC, $this->callD]);

        $this->expectException(AssertionException::class);
        $this->subject->threw($this->matcherFactory->equalTo(new RuntimeException()));
    }

    public function testThrewFailureExpectingMatcherWithNoCalls()
    {
        $this->expectException(AssertionException::class);
        $this->subject->threw($this->matcherFactory->equalTo(new RuntimeException()));
    }

    public function testThrewFailureExpectingMatcherWithNoExceptions()
    {
        $this->subject->setCalls([$this->callA, $this->callB]);

        $this->expectException(AssertionException::class);
        $this->subject->threw($this->matcherFactory->equalTo(new RuntimeException()));
    }

    public function testThrewFailureInvalidInput()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to match exceptions against 111.');
        $this->subject->threw(111);
    }

    public function testThrewFailureInvalidInputObject()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to match exceptions against #0{}.');
        $this->subject->threw((object) []);
    }

    public function testCheckAlwaysThrew()
    {
        $this->assertFalse((bool) $this->subject->always()->checkThrew());
        $this->assertFalse((bool) $this->subject->always()->checkThrew(Exception::class));
        $this->assertFalse((bool) $this->subject->always()->checkThrew(RuntimeException::class));
        $this->assertFalse((bool) $this->subject->always()->checkThrew($this->exceptionA));
        $this->assertFalse((bool) $this->subject->always()->checkThrew($this->exceptionB));
        $this->assertFalse(
            (bool) $this->subject->always()->checkThrew($this->matcherFactory->equalTo($this->exceptionA))
        );
        $this->assertFalse((bool) $this->subject->always()->checkThrew(InvalidArgumentException::class));
        $this->assertFalse((bool) $this->subject->always()->checkThrew(new Exception()));
        $this->assertFalse((bool) $this->subject->always()->checkThrew(new RuntimeException()));
        $this->assertFalse((bool) $this->subject->always()->checkThrew($this->matcherFactory->equalTo(null)));

        $this->subject->setCalls($this->calls);

        $this->assertFalse((bool) $this->subject->always()->checkThrew());
        $this->assertFalse((bool) $this->subject->always()->checkThrew(Exception::class));
        $this->assertFalse((bool) $this->subject->always()->checkThrew(RuntimeException::class));
        $this->assertFalse((bool) $this->subject->always()->checkThrew($this->exceptionA));
        $this->assertFalse((bool) $this->subject->always()->checkThrew($this->exceptionB));
        $this->assertFalse(
            (bool) $this->subject->always()->checkThrew($this->matcherFactory->equalTo($this->exceptionA))
        );
        $this->assertFalse((bool) $this->subject->always()->checkThrew(InvalidArgumentException::class));
        $this->assertFalse((bool) $this->subject->always()->checkThrew(new Exception()));
        $this->assertFalse((bool) $this->subject->always()->checkThrew(new RuntimeException()));
        $this->assertFalse((bool) $this->subject->always()->checkThrew($this->matcherFactory->equalTo(null)));

        $this->subject->setCalls([$this->callC, $this->callC]);

        $this->assertTrue((bool) $this->subject->always()->checkThrew());
        $this->assertTrue((bool) $this->subject->always()->checkThrew(Exception::class));
        $this->assertTrue((bool) $this->subject->always()->checkThrew(RuntimeException::class));
        $this->assertTrue((bool) $this->subject->always()->checkThrew($this->exceptionA));
        $this->assertFalse((bool) $this->subject->always()->checkThrew($this->exceptionB));
        $this->assertTrue(
            (bool) $this->subject->always()->checkThrew($this->matcherFactory->equalTo($this->exceptionA))
        );
        $this->assertFalse((bool) $this->subject->always()->checkThrew(InvalidArgumentException::class));
        $this->assertFalse((bool) $this->subject->always()->checkThrew(new Exception()));
        $this->assertFalse((bool) $this->subject->always()->checkThrew(new RuntimeException()));
        $this->assertFalse((bool) $this->subject->always()->checkThrew($this->matcherFactory->equalTo(null)));

        $this->subject->setCalls([$this->callA, $this->callA]);

        $this->assertFalse((bool) $this->subject->always()->checkThrew($this->matcherFactory->equalTo(null)));
    }

    public function testAlwaysThrew()
    {
        $this->subject->setCalls([$this->callC, $this->callC]);
        $expected = new EventSequence([$this->callCResponse, $this->callCResponse], $this->callVerifierFactory);

        $this->assertEquals($expected, $this->subject->always()->threw());
        $this->assertEquals($expected, $this->subject->always()->threw(Exception::class));
        $this->assertEquals($expected, $this->subject->always()->threw(RuntimeException::class));
        $this->assertEquals($expected, $this->subject->always()->threw($this->exceptionA));
        $this->assertEquals(
            $expected,
            $this->subject->always()->threw($this->matcherFactory->equalTo($this->exceptionA))
        );
    }

    public function testAlwaysThrewFailureExpectingAny()
    {
        $this->subject->setCalls($this->calls);

        $this->expectException(AssertionException::class);
        $this->subject->always()->threw();
    }

    public function testAlwaysThrewFailureExpectingAnyButNothingThrown()
    {
        $this->subject->setCalls([$this->callA, $this->callB]);

        $this->expectException(AssertionException::class);
        $this->subject->always()->threw();
    }

    public function testAlwaysThrewFailureExpectingAnyWithNoCalls()
    {
        $this->expectException(AssertionException::class);
        $this->subject->always()->threw();
    }

    public function testAlwaysThrewFailureExpectingType()
    {
        $this->subject->setCalls([$this->callC, $this->callD]);

        $this->expectException(AssertionException::class);
        $this->subject->always()->threw(UndefinedCallException::class);
    }

    public function testAlwaysThrewFailureExpectingTypeWithNoCalls()
    {
        $this->expectException(AssertionException::class);
        $this->subject->always()->threw(UndefinedCallException::class);
    }

    public function testAlwaysThrewFailureExpectingTypeWithNoExceptions()
    {
        $this->subject->setCalls([$this->callA, $this->callB]);

        $this->expectException(AssertionException::class);
        $this->subject->always()->threw(UndefinedCallException::class);
    }

    public function testAlwaysThrewFailureExpectingException()
    {
        $this->subject->setCalls([$this->callC, $this->callD]);

        $this->expectException(AssertionException::class);
        $this->subject->always()->threw(new RuntimeException());
    }

    public function testAlwaysThrewFailureExpectingExceptionWithNoCalls()
    {
        $this->expectException(AssertionException::class);
        $this->subject->always()->threw(new RuntimeException());
    }

    public function testAlwaysThrewFailureExpectingExceptionWithNoExceptions()
    {
        $this->subject->setCalls([$this->callA, $this->callB]);

        $this->expectException(AssertionException::class);
        $this->subject->always()->threw(new RuntimeException());
    }

    public function testAlwaysThrewFailureExpectingMatcher()
    {
        $this->subject->setCalls([$this->callC, $this->callD]);

        $this->expectException(AssertionException::class);
        $this->subject->always()->threw($this->matcherFactory->equalTo(new RuntimeException()));
    }

    public function testAlwaysThrewFailureExpectingMatcherWithNoCalls()
    {
        $this->expectException(AssertionException::class);
        $this->subject->always()->threw($this->matcherFactory->equalTo(new RuntimeException()));
    }

    public function testAlwaysThrewFailureExpectingMatcherWithNoExceptions()
    {
        $this->subject->setCalls([$this->callA, $this->callB]);

        $this->expectException(AssertionException::class);
        $this->subject->always()->threw($this->matcherFactory->equalTo(new RuntimeException()));
    }

    public function testCheckIterated()
    {
        $this->assertFalse((bool) $this->subject->checkIterated());
        $this->assertTrue((bool) $this->subject->never()->checkIterated());

        $this->subject->setCalls($this->calls);

        $this->assertFalse((bool) $this->subject->checkIterated());
        $this->assertTrue((bool) $this->subject->never()->checkIterated());

        $this->subject->addCall($this->iteratorCall);

        $this->assertTrue((bool) $this->subject->checkIterated());
        $this->assertTrue((bool) $this->subject->once()->checkIterated());
    }

    public function testIterated()
    {
        $this->assertEquals(
            $this->iterableVerifierFactory->create($this->spy, []),
            $this->subject->never()->iterated()
        );

        $this->subject->addCall($this->iteratorCall);

        $this->assertEquals(
            $this->iterableVerifierFactory->create($this->spy, [$this->iteratorCall]),
            $this->subject->iterated()
        );
    }

    public function testIteratedFailure()
    {
        $this->subject->setCalls($this->calls);

        $this->expectException(AssertionException::class);
        $this->subject->iterated();
    }

    public function testIteratedFailureWithNoCalls()
    {
        $this->expectException(AssertionException::class);
        $this->subject->iterated();
    }

    public function testCheckAlwaysIterated()
    {
        $this->assertFalse((bool) $this->subject->always()->checkIterated());

        $this->subject->setCalls($this->calls);

        $this->assertFalse((bool) $this->subject->always()->checkIterated());

        $this->subject->setCalls([$this->iteratorCall, $this->iteratorCall]);

        $this->assertTrue((bool) $this->subject->always()->checkIterated());
    }

    public function testAlwaysIterated()
    {
        $this->subject->setCalls([$this->iteratorCall, $this->iteratorCall]);
        $expected =
            $this->iterableVerifierFactory->create($this->spy, [$this->iteratorCall, $this->iteratorCall]);

        $this->assertEquals($expected, $this->subject->always()->iterated());
    }

    public function testAlwaysIteratedFailure()
    {
        $this->subject->setCalls($this->calls);

        $this->expectException(AssertionException::class);
        $this->subject->always()->iterated();
    }

    public function testAlwaysIteratedFailureWithNoCalls()
    {
        $this->expectException(AssertionException::class);
        $this->subject->always()->iterated();
    }

    public function testCardinalityMethods()
    {
        $this->subject->never();

        $this->assertEquals(new Cardinality(0, 0), $this->subject->never()->cardinality());
        $this->assertEquals(new Cardinality(1, 1), $this->subject->once()->cardinality());
        $this->assertEquals(new Cardinality(2, 2), $this->subject->times(2)->cardinality());
        $this->assertEquals(new Cardinality(2, 2), $this->subject->twice()->cardinality());
        $this->assertEquals(new Cardinality(3, 3), $this->subject->thrice()->cardinality());
        $this->assertEquals(new Cardinality(3), $this->subject->atLeast(3)->cardinality());
        $this->assertEquals(new Cardinality(0, 4), $this->subject->atMost(4)->cardinality());
        $this->assertEquals(new Cardinality(5, 6), $this->subject->between(5, 6)->cardinality());
        $this->assertEquals(new Cardinality(5, 6, true), $this->subject->between(5, 6)->always()->cardinality());
    }

    public function testReturnedFailureWithGenerator()
    {
        $this->subject->addCall($this->generatorCall);

        $this->expectException(AssertionException::class);
        $this->subject->returned(null);
    }

    public function testThrewFailureWithGenerator()
    {
        $this->generatorEndEvent = $this->eventFactory->createReturned(null);
        $this->generatorCall = $this->callFactory->create(
            $this->generatorCalledEvent,
            $this->generatedEvent,
            $this->generatorEvents,
            $this->eventFactory->createThrew($this->exceptionA)
        );
        $this->subject->addCall($this->generatorCall);

        $this->expectException(AssertionException::class);
        $this->subject->threw();
    }

    public function testCheckGenerated()
    {
        $this->assertFalse((bool) $this->subject->checkGenerated());
        $this->assertTrue((bool) $this->subject->never()->checkGenerated());

        $this->subject->setCalls($this->calls);

        $this->assertFalse((bool) $this->subject->checkGenerated());
        $this->assertTrue((bool) $this->subject->never()->checkGenerated());

        $this->subject->addCall($this->generatorCall);

        $this->assertTrue((bool) $this->subject->checkGenerated());
        $this->assertTrue((bool) $this->subject->once()->checkGenerated());
    }

    public function testGenerated()
    {
        $this->assertEquals(
            $this->generatorVerifierFactory->create($this->spy, []),
            $this->subject->never()->generated()
        );

        $this->subject->addCall($this->generatorCall);

        $this->assertEquals(
            $this->generatorVerifierFactory->create($this->spy, [$this->generatorCall]),
            $this->subject->generated()
        );
    }

    public function testGeneratedFailure()
    {
        $this->subject->setCalls($this->calls);
        $this->expectException(AssertionException::class);
        $this->subject->generated();
    }

    public function testGeneratedFailureWithNoCalls()
    {
        $this->expectException(AssertionException::class);
        $this->subject->generated();
    }

    public function testCheckAlwaysGenerated()
    {
        $this->assertFalse((bool) $this->subject->always()->checkGenerated());

        $this->subject->setCalls($this->calls);

        $this->assertFalse((bool) $this->subject->always()->checkGenerated());

        $this->subject->setCalls([$this->generatorCall, $this->generatorCall]);

        $this->assertTrue((bool) $this->subject->always()->checkGenerated());
    }

    public function testAlwaysGenerated()
    {
        $this->subject->setCalls([$this->generatorCall, $this->generatorCall]);
        $expected =
            $this->generatorVerifierFactory->create($this->spy, [$this->generatorCall, $this->generatorCall]);

        $this->assertEquals($expected, $this->subject->always()->generated());
    }

    public function testAlwaysGeneratedFailure()
    {
        $this->subject->setCalls($this->calls);
        $this->expectException(AssertionException::class);
        $this->subject->always()->generated();
    }

    public function testAlwaysGeneratedFailureWithNoMatcher()
    {
        $this->subject->setCalls($this->calls);
        $this->expectException(AssertionException::class);
        $this->subject->always()->generated();
    }

    public function testAlwaysGeneratedFailureWithNoCalls()
    {
        $this->expectException(AssertionException::class);
        $this->subject->always()->generated();
    }
}
