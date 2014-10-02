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
        $this->exporter = new Exporter();
        $this->subject = new AssertionRenderer($this->exporter);

        $this->callFactory = new TestCallFactory();
        $this->callA = $this->callFactory->create(
            array(
                $this->callFactory->createCalledEvent(array($this, 'setUp'), array('a', 'b')),
                $this->callFactory->createReturnedEvent('x'),
            )
        );
        $this->callB = $this->callFactory->create(
            array(
                $this->callFactory->createCalledEvent('implode'),
                $this->callFactory->createThrewEvent(new RuntimeException('You done goofed.')),
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

    public function testRenderCalls()
    {
        $expected = <<<'EOD'
    - Eloquent\Phony\Assertion\Renderer\AssertionRendererTest->setUp('a', 'b')
    - implode()
EOD;

        $this->assertSame('', $this->subject->renderCalls(array()));
        $this->assertSame($expected, $this->subject->renderCalls(array($this->callA, $this->callB)));
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

    public function testRenderReturnValues()
    {
        $expected = <<<'EOD'
    - 'x'
    - null
EOD;

        $this->assertSame('', $this->subject->renderReturnValues(array()));
        $this->assertSame($expected, $this->subject->renderReturnValues(array($this->callA, $this->callB)));
    }

    public function testRenderThrownExceptions()
    {
        $expected = <<<'EOD'
    - <none>
    - RuntimeException('You done goofed.')
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
        $callFactory = new TestCallFactory();

        return array(
            'Method' => array(
                $callFactory->create(array($callFactory->createCalledEvent(array($this, 'setUp')))),
                "Eloquent\Phony\Assertion\Renderer\AssertionRendererTest->setUp()",
            ),
            'Static method' => array(
                $callFactory->create(array($callFactory->createCalledEvent('ReflectionMethod::export'))),
                "ReflectionMethod::export()",
            ),
            'Function' => array(
                $callFactory->create(array($callFactory->createCalledEvent('implode'))),
                "implode()",
            ),
            'Closure' => array(
                $callFactory->create(array($callFactory->createCalledEvent(function () {}))),
                "Eloquent\Phony\Assertion\Renderer\{closure}()",
            ),
            'With arguments' => array(
                $callFactory->create(array($callFactory->createCalledEvent('implode', array('a', 111)))),
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

    public function testRenderException()
    {
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
