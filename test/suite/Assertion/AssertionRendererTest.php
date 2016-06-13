<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Assertion;

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Call\Call;
use Eloquent\Phony\Event\EventSequence;
use Eloquent\Phony\Event\NullEvent;
use Eloquent\Phony\Exporter\InlineExporter;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Matcher\MatcherFactory;
use Eloquent\Phony\Mock\Builder\MockBuilderFactory;
use Eloquent\Phony\Mock\Handle\HandleFactory;
use Eloquent\Phony\Spy\Spy;
use Eloquent\Phony\Spy\SpyFactory;
use Eloquent\Phony\Stub\Stub;
use Eloquent\Phony\Stub\StubFactory;
use Eloquent\Phony\Test\TestCallFactory;
use Eloquent\Phony\Test\TestClassA;
use Eloquent\Phony\Test\TestEvent;
use Eloquent\Phony\Verification\Cardinality;
use Exception;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use RuntimeException;

class AssertionRendererTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $exporterReflector = new ReflectionClass('Eloquent\Phony\Exporter\InlineExporter');
        $property = $exporterReflector->getProperty('incrementIds');
        $property->setAccessible(true);
        $property->setValue(InlineExporter::instance(), false);

        $this->invocableInspector = new InvocableInspector();
        $this->exporter = new InlineExporter(1, false);
        $this->subject = new AssertionRenderer($this->invocableInspector, $this->exporter);
        $this->handleFactory = HandleFactory::instance();

        $this->thisObjectA = new TestClassA();

        $mockBuilderFactory = MockBuilderFactory::instance();

        $mockBuilder = $mockBuilderFactory->create('Eloquent\Phony\Test\TestClassA');
        $this->thisObjectB = $mockBuilder->get();
        $this->thisObjectBHandle = $this->handleFactory->createStubbing($this->thisObjectB);
        $this->thisObjectBHandle->setLabel('label');
        $this->thisObjectB->testClassAMethodA();

        $mockBuilder = $mockBuilderFactory->create('IteratorAggregate');
        $mockBuilder->named('PhonyMockAssertionRendererTestIteratorAggregate');
        $this->thisObjectC = $mockBuilder->get();
        $this->thisObjectCHandle = $this->handleFactory->createStubbing($this->thisObjectC);
        $this->thisObjectCHandle->setLabel('label');

        $this->callFactory = new TestCallFactory();
        $this->callEventFactory = $this->callFactory->eventFactory();
        $this->callA = $this->callFactory->create(
            $this->callEventFactory
                ->createCalled(array($this->thisObjectA, 'testClassAMethodA'), Arguments::create('a', 'b')),
            $this->callEventFactory->createReturned('x'),
            null,
            $this->callEventFactory->createReturned('x')
        );
        $this->callB = $this->callFactory->create(
            $this->callEventFactory->createCalled('implode'),
            $this->callEventFactory->createThrew(new RuntimeException('You done goofed.')),
            null,
            $this->callEventFactory->createThrew(new RuntimeException('You done goofed.'))
        );
        $this->callC = $this->callFactory->create(
            $this->callEventFactory->createCalled('implode')
        );
        $this->callD = $this->callFactory->create(
            $this->callEventFactory->createCalled(array($this->thisObjectB, 'testClassAMethodA'))
        );
        $this->callE = $this->callFactory->create(
            $this->callEventFactory->createCalled(array($this->thisObjectC, 'getIterator'))
        );
        $this->callF = $this->thisObjectBHandle->testClassAMethodA->callAt();

        $this->matcherFactory = MatcherFactory::instance();
    }

    protected function tearDown()
    {
        $exporterReflector = new ReflectionClass('Eloquent\Phony\Exporter\InlineExporter');
        $property = $exporterReflector->getProperty('incrementIds');
        $property->setAccessible(true);
        $property->setValue(InlineExporter::instance(), true);
    }

    public function testRenderValue()
    {
        $this->assertSame('"x"', $this->subject->renderValue('x'));
        $this->assertSame('111', $this->subject->renderValue(111));
        $this->assertSame('"x\ny"', $this->subject->renderValue("x\ny"));
        $this->assertSame(
            '"12345678901234567890123456789012345678901234567890"',
            $this->subject->renderValue('12345678901234567890123456789012345678901234567890')
        );
    }

    public function testRenderMock()
    {
        $this->assertSame('TestClassA[label]', $this->subject->renderMock($this->thisObjectBHandle));
    }

    public function testRenderMockStatic()
    {
        $handle = $this->handleFactory->createStubbingStatic($this->thisObjectB);

        $this->assertSame('TestClassA[static]', $this->subject->renderMock($handle));
    }

    public function testRenderMockWithoutParentClass()
    {
        $this->assertSame(
            'PhonyMockAssertionRendererTestIteratorAggregate[label]',
            $this->subject->renderMock($this->thisObjectCHandle)
        );
    }

    public function testRenderMatchers()
    {
        $matcherA = $this->matcherFactory->equalTo('a');
        $matcherB = $this->matcherFactory->equalTo(111);

        $this->assertSame('<none>', $this->subject->renderMatchers(array()));
        $this->assertSame('"a"', $this->subject->renderMatchers(array($matcherA)));
        $this->assertSame('"a", 111', $this->subject->renderMatchers(array($matcherA, $matcherB)));
    }

    public function testRenderCalls()
    {
        $expected = <<<'EOD'
    - TestClassA->testClassAMethodA("a", "b")
    - implode()
    - TestClassA->testClassAMethodA()
    - IteratorAggregate->getIterator()
    - TestClassA[label]->testClassAMethodA()
EOD;

        $this->assertSame('', $this->subject->renderCalls(array()));
        $this->assertSame(
            $expected,
            $this->subject->renderCalls(array($this->callA, $this->callB, $this->callD, $this->callE, $this->callF))
        );
    }

    public function testRenderThisValues()
    {
        $expected = <<<'EOD'
    - Eloquent\Phony\Test\TestClassA#0{constructorArguments: #0[]}
    - null
EOD;

        $this->assertSame('', $this->subject->renderThisValues(array()));
        $this->assertSame($expected, $this->subject->renderThisValues(array($this->callA, $this->callB)));
    }

    public function testRenderCallsArguments()
    {
        $expected = <<<'EOD'
Call #0:
    - "a"
    - "b"
Call #1:
    <none>
EOD;

        $this->assertSame('', $this->subject->renderCallsArguments(array()));
        $this->assertSame($expected, $this->subject->renderCallsArguments(array($this->callA, $this->callB)));
    }

    public function testRenderResponses()
    {
        $expected = <<<'EOD'
    - returned "x"
    - threw RuntimeException("You done goofed.")
    - <none>
EOD;

        $this->assertSame('', $this->subject->renderResponses(array()));
        $this->assertSame($expected, $this->subject->renderResponses(array($this->callA, $this->callB, $this->callC)));
    }

    public function testRenderResponsesExpandedTraversables()
    {
        $traversableCallA = $this->callFactory->create(
            $this->callEventFactory->createCalled(),
            $this->callEventFactory->createReturned(array('a' => 'b', 'c' => 'd')),
            array(
                $this->callEventFactory->createProduced('a', 'b'),
                $this->callEventFactory->createProduced('c', 'd'),
            ),
            $this->callEventFactory->createConsumed()
        );
        $traversableCallB = $this->callFactory->create(
            $this->callEventFactory->createCalled(),
            $this->callEventFactory->createReturned(array('a' => 'b', 'c' => 'd')),
            array(
                $this->callEventFactory->createProduced('a', 'b'),
            )
        );
        $expected = <<<'EOD'
    - returned "x"
    - returned #0[:2] producing:
        - produced "a": "b"
        - produced "c": "d"
        - finished iterating
    - returned #0[:2] producing:
        - produced "a": "b"
        - did not finish iterating
    - threw RuntimeException("You done goofed.")
    - <none>
EOD;

        $this->assertSame('', $this->subject->renderResponses(array(), true));
        $this->assertSame(
            $expected,
            $this->subject->renderResponses(
                array($this->callA, $traversableCallA, $traversableCallB, $this->callB, $this->callC),
                true
            )
        );
    }

    public function renderCallData()
    {
        $callFactory = new TestCallFactory();
        $callEventFactory = $callFactory->eventFactory();

        $spyFactory = SpyFactory::instance();

        $spyWithLabel = $spyFactory->create();
        $spyWithLabel->setLabel('label');

        $stubFactory = StubFactory::instance();

        $stubWithLabel = $stubFactory->create();
        $stubWithLabel->setLabel('label');

        return array(
            'Method' => array(
                $callFactory->create($callEventFactory->createCalled(array($this, 'setUp'))),
                'AssertionRendererTest->setUp()',
            ),
            'Static method' => array(
                $callFactory->create($callEventFactory->createCalled('ReflectionMethod::export')),
                'ReflectionMethod::export()',
            ),
            'Function' => array(
                $callFactory->create($callEventFactory->createCalled('implode')),
                'implode()',
            ),
            'Closure' => array(
                $callFactory->create($callEventFactory->createCalled(function () {})),
                'Eloquent\Phony\Assertion\{closure}()',
            ),
            'Spy' => array(
                $callFactory->create($callEventFactory->createCalled($spyFactory->create()->setLabel(null))),
                '{spy}()',
            ),
            'Spy with label' => array(
                $callFactory->create($callEventFactory->createCalled($spyWithLabel)),
                '{spy}[label]()',
            ),
            'Stub' => array(
                $callFactory->create($callEventFactory->createCalled($stubFactory->create()->setLabel(null))),
                '{stub}()',
            ),
            'Stub with label' => array(
                $callFactory->create($callEventFactory->createCalled($stubWithLabel)),
                '{stub}[label]()',
            ),
            'With arguments' => array(
                $callFactory->create($callEventFactory->createCalled('implode', Arguments::create('a', 111))),
                'implode("a", 111)',
            ),
        );
    }

    /**
     * @dataProvider renderCallData
     */
    public function testRenderCall($call, $expected)
    {
        $this->assertSame($expected, $this->subject->renderCall($call));
    }

    public function renderResponseData()
    {
        $callFactory = new TestCallFactory();
        $callEventFactory = $callFactory->eventFactory();

        return array(
            'Returned' => array(
                $callFactory->create($callEventFactory->createCalled(), $callEventFactory->createReturned('a')),
                'Returned "a".',
            ),
            'Threw' => array(
                $callFactory->create(
                    $callEventFactory->createCalled(),
                    $callEventFactory->createThrew(new RuntimeException('You done goofed.'))
                ),
                'Threw RuntimeException("You done goofed.").',
            ),
            'Never responded' => array(
                $callFactory->create($callEventFactory->createCalled()),
                'Never responded.',
            ),
        );
    }

    /**
     * @dataProvider renderResponseData
     */
    public function testRenderResponse($call, $expected)
    {
        $this->assertSame($expected, $this->subject->renderResponse($call));
    }

    public function testRenderResponseWithExapandedTraversables()
    {
        $traversableCall = $this->callFactory->create(
            $this->callEventFactory->createCalled(),
            $this->callEventFactory->createReturned(array('a' => 'b', 'c' => 'd')),
            array(
                $this->callEventFactory->createProduced('a', 'b'),
                $this->callEventFactory->createProduced('c', 'd'),
            ),
            $this->callEventFactory->createConsumed()
        );
        $expected = <<<'EOD'
Returned #0[:2] producing:
    - produced "a": "b"
    - produced "c": "d"
    - finished iterating
EOD;

        $this->assertSame($expected, $this->subject->renderResponse($traversableCall, true));
    }

    public function testRenderException()
    {
        $this->assertSame('Exception()', $this->subject->renderException(new Exception()));
        $this->assertSame('RuntimeException()', $this->subject->renderException(new RuntimeException()));
        $this->assertSame(
            'Exception("You done goofed.")',
            $this->subject->renderException(new Exception('You done goofed.'))
        );
    }

    public function testRenderEvents()
    {
        $events = new EventSequence(
            array(
                $this->callA,
                $this->callA->calledEvent(),
                $this->callA->responseEvent(),
                $this->callB->responseEvent(),
                $this->callEventFactory->createProduced('x', 'y'),
                $this->callEventFactory->createReceived('z'),
                $this->callEventFactory
                    ->createReceivedException(new RuntimeException('Consequences will never be the same.')),
                $this->callEventFactory->createConsumed(),
                NullEvent::instance(),
                new TestEvent(0, 0.0),
            )
        );
        $expected = <<<'EOD'
    - called TestClassA->testClassAMethodA("a", "b")
    - called TestClassA->testClassAMethodA("a", "b")
    - returned "x" from TestClassA->testClassAMethodA("a", "b")
    - threw RuntimeException("You done goofed.") in implode()
    - produced "x": "y" from unknown call
    - received "z" in unknown call
    - received exception RuntimeException("Consequences will never be the same.") in unknown call
    - unknown call finished iterating
    - <none>
    - "Eloquent\\Phony\\Test\\TestEvent" event
EOD;

        $this->assertSame($expected, $this->subject->renderEvents($events));
    }

    public function renderCardinalityData()
    {
        //                                                minimum maximum isAlways verb      expected
        return array(
            'Null minimum, null maximum'         => array(null,   null,   false,   'return', 'return, any number of times'),
            'Zero minimum, null maximum'         => array(0,      null,   false,   'return', 'return, any number of times'),
            'One minimum, null maximum'          => array(1,      null,   false,   'return', 'return'),
            'Two minimum, null maximum'          => array(2,      null,   false,   'return', 'return, 2 times'),

            'Null minimum, zero maximum'         => array(null,   0,      false,   'return', 'no return'),
            'Zero minimum, zero maximum'         => array(0,      0,      false,   'return', 'no return'),

            'Null minimum, one maximum'          => array(null,   1,      false,   'return', 'return, up to 1 time'),
            'Zero minimum, one maximum'          => array(0,      1,      false,   'return', 'return, up to 1 time'),
            'One minimum, one maximum'           => array(1,      1,      false,   'return', 'return, exactly 1 time'),

            'Null minimum, two maximum'          => array(null,   2,      false,   'return', 'return, up to 2 times'),
            'Zero minimum, two maximum'          => array(0,      2,      false,   'return', 'return, up to 2 times'),
            'One minimum, two maximum'           => array(1,      2,      false,   'return', 'return, between 1 and 2 times'),
            'Two minimum, two maximum'           => array(2,      2,      false,   'return', 'return, exactly 2 times'),

            'Null minimum, null maximum, always' => array(null,   null,   true,    'return', 'every return, any number of times'),
            'Zero minimum, null maximum, always' => array(0,      null,   true,    'return', 'every return, any number of times'),
            'One minimum, null maximum, always'  => array(1,      null,   true,    'return', 'every return'),
            'Two minimum, null maximum, always'  => array(2,      null,   true,    'return', 'every return, 2 times'),

            'Null minimum, one maximum, always'  => array(null,   1,      true,    'return', 'every return, up to 1 time'),
            'Zero minimum, one maximum, always'  => array(0,      1,      true,    'return', 'every return, up to 1 time'),
            'One minimum, one maximum, always'   => array(1,      1,      true,    'return', 'every return, exactly 1 time'),

            'Null minimum, two maximum, always'  => array(null,   2,      true,    'return', 'every return, up to 2 times'),
            'Zero minimum, two maximum, always'  => array(0,      2,      true,    'return', 'every return, up to 2 times'),
            'One minimum, two maximum, always'   => array(1,      2,      true,    'return', 'every return, between 1 and 2 times'),
            'Two minimum, two maximum, always'   => array(2,      2,      true,    'return', 'every return, exactly 2 times'),
        );
    }

    /**
     * @dataProvider renderCardinalityData
     */
    public function testRenderCardinality($minimum, $maximum, $isAlways, $verb, $expected)
    {
        $this->assertSame(
            $expected,
            $this->subject->renderCardinality(new Cardinality($minimum, $maximum, $isAlways), $verb)
        );
    }

    public function testInstance()
    {
        $class = get_class($this->subject);
        $reflector = new ReflectionClass($class);
        $property = $reflector->getProperty('instance');
        $property->setAccessible(true);
        $property->setValue(null, null);
        $instance = $class::instance();

        $this->assertInstanceOf($class, $instance);
        $this->assertSame($instance, $class::instance());
    }
}
