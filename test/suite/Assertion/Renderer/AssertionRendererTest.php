<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Assertion\Renderer;

use Eloquent\Phony\Call\Call;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Matcher\EqualToMatcher;
use Eloquent\Phony\Test\TestCallFactory;
use Exception;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use RuntimeException;
use SebastianBergmann\Exporter\Exporter;

class AssertionRendererTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->invocableInspector = new InvocableInspector();
        $this->exporter = new Exporter();
        $this->subject = new AssertionRenderer($this->invocableInspector, $this->exporter);

        $this->callFactory = new TestCallFactory();
        $this->callEventFactory = $this->callFactory->eventFactory();
        $this->callA = $this->callFactory->create(
            $this->callEventFactory->createCalled(array($this, 'setUp'), array('a', 'b')),
            $this->callEventFactory->createReturned('x')
        );
        $this->callB = $this->callFactory->create(
            $this->callEventFactory->createCalled('implode'),
            $this->callEventFactory->createThrew(new RuntimeException('You done goofed.'))
        );
        $this->callC = $this->callFactory->create(
            $this->callEventFactory->createCalled('implode')
        );
    }

    public function testConstructor()
    {
        $this->assertSame($this->invocableInspector, $this->subject->invocableInspector());
        $this->assertSame($this->exporter, $this->subject->exporter());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new AssertionRenderer();

        $this->assertSame(InvocableInspector::instance(), $this->subject->invocableInspector());
        $this->assertEquals($this->exporter, $this->subject->exporter());
    }

    public function testRenderValue()
    {
        $this->assertSame("'x'", $this->subject->renderValue('x'));
        $this->assertSame("111", $this->subject->renderValue(111));
        $this->assertSame("'x\ny'", $this->subject->renderValue("x\ny"));
        $this->assertSame(
            "'12345678901234567890123456789012345678901234567890'",
            $this->subject->renderValue('12345678901234567890123456789012345678901234567890')
        );
    }

    public function testRenderMatchers()
    {
        $matcherA = new EqualToMatcher('a');
        $matcherB = new EqualToMatcher(111);

        $this->assertSame("<none>", $this->subject->renderMatchers(array()));
        $this->assertSame("<'a'>", $this->subject->renderMatchers(array($matcherA)));
        $this->assertSame("<'a'>, <111>", $this->subject->renderMatchers(array($matcherA, $matcherB)));
    }

    public function renderCardinalityData()
    {
        //                                        minimum maximum singular plural   expected
        return array(
            'Null minimum, null maximum' => array(null,   null,   'thing', null,    'any amount of things'),
            'Zero minimum, null maximum' => array(0,      null,   'thing', null,    'any amount of things'),
            'One minimum, null maximum'  => array(1,      null,   'thing', null,    'thing'),
            'Two minimum, null maximum'  => array(2,      null,   'thing', null,    '2 things'),

            'Null minimum, zero maximum' => array(null,   0,      'thing', null,    '0 things'),
            'Zero minimum, zero maximum' => array(0,      0,      'thing', null,    '0 things'),

            'Null minimum, one maximum'  => array(null,   1,      'thing', null,    'up to 1 thing'),
            'Zero minimum, one maximum'  => array(0,      1,      'thing', null,    'up to 1 thing'),
            'One minimum, one maximum'   => array(1,      1,      'thing', null,    'exactly 1 thing'),

            'Null minimum, two maximum'  => array(null,   2,      'thing', null,    'up to 2 things'),
            'Zero minimum, two maximum'  => array(0,      2,      'thing', null,    'up to 2 things'),
            'One minimum, two maximum'   => array(1,      2,      'thing', null,    'between 1 and 2 things'),
            'Two minimum, two maximum'   => array(2,      2,      'thing', null,    'exactly 2 things'),

            'Custom plural'              => array(2,      null,   'sheep', 'sheep', '2 sheep'),
        );
    }

    /**
     * @dataProvider renderCardinalityData
     */
    public function testRenderCardinality($minimum, $maximum, $singular, $plural, $expected)
    {
        $this->assertSame($expected, $this->subject->renderCardinality(array($minimum, $maximum), $singular, $plural));
    }

    public function testRenderCardinalityFailure()
    {
        $this->setExpectedException('InvalidArgumentException', 'Invalid cardinality.');
        $this->subject->renderCardinality(array(1, 0), 'thing');
    }

    public function testRenderCalls()
    {
        $expected = <<<'EOD'
    - Eloquent\Phony\Assertion\Renderer\AssertionRendererTest->setUp('a', 'b')
    - implode()
EOD;

        $this->assertSame('', $this->subject->renderCalls(array()));
        $this->assertSame($expected, $this->subject->renderCalls(array($this->callA, $this->callB)));
    }

    public function testRenderThisValues()
    {
        $expected = <<<'EOD'
    - Eloquent\Phony\Assertion\Renderer\AssertionRendererTest Object (...)
    - null
EOD;

        $this->assertSame('', $this->subject->renderThisValues(array()));
        $this->assertSame($expected, $this->subject->renderThisValues(array($this->callA, $this->callB)));
    }

    public function testRenderCallsArguments()
    {
        $expected = <<<'EOD'
    - 'a', 'b'
    - <none>
EOD;

        $this->assertSame('', $this->subject->renderCallsArguments(array()));
        $this->assertSame($expected, $this->subject->renderCallsArguments(array($this->callA, $this->callB)));
    }

    public function testRenderResponses()
    {
        $expected = <<<'EOD'
    - returned 'x'
    - threw RuntimeException('You done goofed.')
    - <none>
EOD;

        $this->assertSame('', $this->subject->renderResponses(array()));
        $this->assertSame($expected, $this->subject->renderResponses(array($this->callA, $this->callB, $this->callC)));
    }

    public function renderCallData()
    {
        $callFactory = new TestCallFactory();
        $callEventFactory = $callFactory->eventFactory();

        return array(
            'Method' => array(
                $callFactory->create($callEventFactory->createCalled(array($this, 'setUp'))),
                "Eloquent\Phony\Assertion\Renderer\AssertionRendererTest->setUp()",
            ),
            'Static method' => array(
                $callFactory->create($callEventFactory->createCalled('ReflectionMethod::export')),
                "ReflectionMethod::export()",
            ),
            'Function' => array(
                $callFactory->create($callEventFactory->createCalled('implode')),
                "implode()",
            ),
            'Closure' => array(
                $callFactory->create($callEventFactory->createCalled(function () {})),
                "Eloquent\Phony\Assertion\Renderer\{closure}()",
            ),
            'With arguments' => array(
                $callFactory->create($callEventFactory->createCalled('implode', array('a', 111))),
                "implode('a', 111)",
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
                "Returned 'a'.",
            ),
            'Threw' => array(
                $callFactory->create(
                    $callEventFactory->createCalled(),
                    $callEventFactory->createThrew(new RuntimeException('You done goofed.'))
                ),
                "Threw RuntimeException('You done goofed.').",
            ),
            'Never responded' => array(
                $callFactory->create($callEventFactory->createCalled()),
                "Never responded.",
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

    public function testRenderException()
    {
        $this->assertSame("<none>", $this->subject->renderException());
        $this->assertSame("Exception()", $this->subject->renderException(new Exception()));
        $this->assertSame("RuntimeException()", $this->subject->renderException(new RuntimeException()));
        $this->assertSame(
            "Exception('You done goofed.')",
            $this->subject->renderException(new Exception('You done goofed.'))
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
