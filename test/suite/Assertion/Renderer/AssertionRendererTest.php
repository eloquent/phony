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
use Eloquent\Phony\Call\Event\CalledEvent;
use Eloquent\Phony\Call\Event\ReturnedEvent;
use Eloquent\Phony\Call\Event\ThrewEvent;
use Eloquent\Phony\Matcher\EqualToMatcher;
use Exception;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use RuntimeException;
use SebastianBergmann\Exporter\Exporter;

class AssertionRendererTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->exporter = new Exporter();
        $this->subject = new AssertionRenderer($this->exporter);

        $this->callA = new Call(
            array(
                new CalledEvent(new ReflectionMethod(__METHOD__), $this, array('argumentA', 'argumentB'), 0, .1),
                new ReturnedEvent('returnValue', 1, .2),
            )
        );
        $this->callB = new Call(
            array(
                new CalledEvent(new ReflectionMethod(__METHOD__), null, array(), 2, .3),
                new ThrewEvent(new RuntimeException('message'), 3, .4),
            )
        );
    }

    public function testConstructor()
    {
        $this->assertSame($this->exporter, $this->subject->exporter());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new AssertionRenderer();

        $this->assertEquals($this->exporter, $this->subject->exporter());
    }

    public function testRenderValue()
    {
        $this->assertSame("'value'", $this->subject->renderValue('value'));
        $this->assertSame("111", $this->subject->renderValue(111));
        $this->assertSame("'line\nline'", $this->subject->renderValue("line\nline"));
        $this->assertSame(
            "'12345678901234567890123456789012345678901234567890'",
            $this->subject->renderValue('12345678901234567890123456789012345678901234567890')
        );
    }

    public function testRenderMatchers()
    {
        $matcherA = new EqualToMatcher('argumentA');
        $matcherB = new EqualToMatcher(111);

        $this->assertSame("<none>", $this->subject->renderMatchers(array()));
        $this->assertSame("<'argumentA'>", $this->subject->renderMatchers(array($matcherA)));
        $this->assertSame("<'argumentA'>, <111>", $this->subject->renderMatchers(array($matcherA, $matcherB)));
    }

    public function testRenderCalls()
    {
        $expected = <<<'EOD'
    - Eloquent\Phony\Assertion\Renderer\AssertionRendererTest->setUp('argumentA', 'argumentB')
    - Eloquent\Phony\Assertion\Renderer\AssertionRendererTest->setUp()
EOD;

        $this->assertSame('', $this->subject->renderCalls(array()));
        $this->assertSame($expected, $this->subject->renderCalls(array($this->callA, $this->callB)));
    }

    public function testRenderCallsArguments()
    {
        $expected = <<<'EOD'
    - 'argumentA', 'argumentB'
    - <none>
EOD;

        $this->assertSame('', $this->subject->renderCallsArguments(array()));
        $this->assertSame($expected, $this->subject->renderCallsArguments(array($this->callA, $this->callB)));
    }

    public function testRenderReturnValues()
    {
        $expected = <<<'EOD'
    - 'returnValue'
    - null
EOD;

        $this->assertSame('', $this->subject->renderReturnValues(array()));
        $this->assertSame($expected, $this->subject->renderReturnValues(array($this->callA, $this->callB)));
    }

    public function testRenderThrownExceptions()
    {
        $expected = <<<'EOD'
    - <none>
    - RuntimeException('message')
EOD;

        $this->assertSame('', $this->subject->renderThrownExceptions(array()));
        $this->assertSame($expected, $this->subject->renderThrownExceptions(array($this->callA, $this->callB)));
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

    public function renderCallData()
    {
        return array(
            'Method' => array(
                new Call(array(new CalledEvent(new ReflectionMethod(__METHOD__), $this, array(), 0, .1))),
                "Eloquent\Phony\Assertion\Renderer\AssertionRendererTest->renderCallData()",
            ),
            'Static method' => array(
                new Call(
                    array(new CalledEvent(new ReflectionMethod('ReflectionMethod::export'), null, array(), 0, .1))
                ),
                "ReflectionMethod::export()",
            ),
            'Function' => array(
                new Call(array(new CalledEvent(new ReflectionFunction('function_exists'), null, array(), 0, .1))),
                "function_exists()",
            ),
            'Closure' => array(
                new Call(array(new CalledEvent(new ReflectionFunction(function () {}), $this, array(), 0, .1))),
                "Eloquent\Phony\Assertion\Renderer\{closure}()",
            ),
            'With arguments' => array(
                new Call(
                    array(
                        new CalledEvent(new ReflectionFunction('function_exists'), null, array('argument', 111), 0, .1)
                    )
                ),
                "function_exists('argument', 111)",
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

    public function testRenderException()
    {
        $this->assertSame("Exception()", $this->subject->renderException(new Exception()));
        $this->assertSame("RuntimeException()", $this->subject->renderException(new RuntimeException()));
        $this->assertSame("Exception('message')", $this->subject->renderException(new Exception('message')));
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
