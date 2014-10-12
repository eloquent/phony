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

use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Test\TestCallFactory;
use Exception;
use PHPUnit_Framework_TestCase;
use RuntimeException;
use SebastianBergmann\Exporter\Exporter;

class AssertionRendererWithGeneratorsTest extends PHPUnit_Framework_TestCase
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

        // additions for generators

        $this->generatorCall = $this->callFactory->create(
            $this->callEventFactory->createCalled(),
            $this->callEventFactory->createGenerated(),
            array(
                $this->callEventFactory->createYielded('m', 'n'),
                $this->callEventFactory->createSent('o'),
                $this->callEventFactory->createYielded('p', 'q'),
                $this->callEventFactory
                    ->createSentException(new RuntimeException('Consequences will never be the same.')),
                $this->callEventFactory->createYielded('r', 's'),
                $this->callEventFactory->createSent('t'),
            ),
            $this->callEventFactory->createReturned()
        );
    }

    public function testRenderResponsesWithGenerators()
    {
        $expected = <<<'EOD'
    - returned 'x'
    - returned generator
    - threw RuntimeException('You done goofed.')
EOD;

        $this->assertSame(
            $expected,
            $this->subject->renderResponses(array($this->callA, $this->generatorCall, $this->callB))
        );
    }

    public function testRenderResponsesWithGeneratorsExpanded()
    {
        $expected = <<<'EOD'
    - returned 'x'
    - generated:
        - yielded 'm' => 'n'
        - sent 'o'
        - yielded 'p' => 'q'
        - sent exception RuntimeException('Consequences will never be the same.')
        - yielded 'r' => 's'
        - sent 't'
    - threw RuntimeException('You done goofed.')
EOD;

        $this->assertSame(
            $expected,
            $this->subject->renderResponses(array($this->callA, $this->generatorCall, $this->callB), true)
        );
    }

    public function testRenderGenerated()
    {
        $expected = <<<'EOD'
    - yielded 'm' => 'n'
    - sent 'o'
    - yielded 'p' => 'q'
    - sent exception RuntimeException('Consequences will never be the same.')
    - yielded 'r' => 's'
    - sent 't'
EOD;

        $this->assertSame($expected, $this->subject->renderGenerated($this->generatorCall));
    }
}
