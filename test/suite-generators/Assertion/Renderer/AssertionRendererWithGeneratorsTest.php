<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Assertion\Renderer;

use Eloquent\Phony\Exporter\InlineExporter;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Test\TestCallFactory;
use Exception;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use RuntimeException;

class AssertionRendererWithGeneratorsTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $exporterReflector = new ReflectionClass('Eloquent\Phony\Exporter\InlineExporter');
        $property = $exporterReflector->getProperty('incrementIds');
        $property->setAccessible(true);
        $property->setValue(InlineExporter::instance(), false);

        $this->invocableInspector = new InvocableInspector();
        $this->exporter = new InlineExporter();
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

        // additions for generators

        $this->generatorCall = $this->callFactory->create(
            $this->callEventFactory->createCalled(),
            $this->callEventFactory->createGenerated(),
            array(
                $this->callEventFactory->createProduced('m', 'n'),
                $this->callEventFactory->createReceived('o'),
                $this->callEventFactory->createProduced('p', 'q'),
                $this->callEventFactory
                    ->createReceivedException(new RuntimeException('Consequences will never be the same.')),
                $this->callEventFactory->createProduced('r', 's'),
                $this->callEventFactory->createReceived('t'),
            ),
            $this->callEventFactory->createReturned()
        );
    }

    public function testRenderResponsesWithGenerators()
    {
        $expected = <<<'EOD'
    - returned "x"
    - returned Generator#0{}
    - threw RuntimeException("You done goofed.")
EOD;

        $this->assertSame(
            $expected,
            $this->subject->renderResponses(array($this->callA, $this->generatorCall, $this->callB))
        );
    }

    public function testRenderResponsesWithGeneratorsExpandedTraversables()
    {
        $expected = <<<'EOD'
    - returned "x"
    - generated:
        - produced "m": "n"
        - received "o"
        - produced "p": "q"
        - received exception RuntimeException("Consequences will never be the same.")
        - produced "r": "s"
        - received "t"
    - threw RuntimeException("You done goofed.")
EOD;

        $this->assertSame(
            $expected,
            $this->subject->renderResponses(array($this->callA, $this->generatorCall, $this->callB), true)
        );
    }

    public function testRenderProduced()
    {
        $expected = <<<'EOD'
    - produced "m": "n"
    - received "o"
    - produced "p": "q"
    - received exception RuntimeException("Consequences will never be the same.")
    - produced "r": "s"
    - received "t"
EOD;

        $this->assertSame($expected, $this->subject->renderProduced($this->generatorCall));
    }
}
