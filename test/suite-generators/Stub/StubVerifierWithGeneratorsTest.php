<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub;

use Eloquent\Phony\Assertion\Recorder\AssertionRecorder;
use Eloquent\Phony\Assertion\Renderer\AssertionRenderer;
use Eloquent\Phony\Call\Factory\CallVerifierFactory;
use Eloquent\Phony\Exporter\InlineExporter;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Spy\Factory\SpyFactory;
use Eloquent\Phony\Spy\Spy;
use Eloquent\Phony\Stub\Answer\Builder\Factory\GeneratorAnswerBuilderFactory;
use Eloquent\Phony\Stub\Factory\StubFactory;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class StubVerifierWithGeneratorsTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $exporterReflector = new ReflectionClass('Eloquent\Phony\Exporter\InlineExporter');
        $property = $exporterReflector->getProperty('incrementIds');
        $property->setAccessible(true);
        $property->setValue(InlineExporter::instance(), false);

        $this->callback = 'implode';
        $this->self = (object) array();
        $this->label = 'label';
        $this->stubFactory = StubFactory::instance();
        $this->stub = $this->stubFactory->create($this->callback, $this->self)->setLabel($this->label);
        $this->spyFactory = SpyFactory::instance();
        $this->spy = $this->spyFactory->create($this->stub);
        $this->matcherFactory = MatcherFactory::instance();
        $this->matcherVerifier = new MatcherVerifier();
        $this->callVerifierFactory = CallVerifierFactory::instance();
        $this->assertionRecorder = AssertionRecorder::instance();
        $this->assertionRenderer = AssertionRenderer::instance();
        $this->invocableInspector = InvocableInspector::instance();
        $this->invoker = new Invoker();
        $this->generatorAnswerBuilderFactory = GeneratorAnswerBuilderFactory::instance();
        $this->subject = new StubVerifier(
            $this->stub,
            $this->spy,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector,
            $this->invoker,
            $this->generatorAnswerBuilderFactory
        );

        $this->callsA = array();
        $callsA = &$this->callsA;
        $this->callCountA = 0;
        $callCountA = &$this->callCountA;
        $this->callbackA = function () use (&$callsA, &$callCountA) {
            $arguments = func_get_args();
            $callsA[] = $arguments;
            ++$callCountA;

            array_unshift($arguments, 'A');

            return $arguments;
        };

        $this->callsB = array();
        $callsB = &$this->callsB;
        $this->callCountB = 0;
        $callCountB = &$this->callCountB;
        $this->callbackB = function () use (&$callsB, &$callCountB) {
            $arguments = func_get_args();
            $callsB[] = $arguments;
            ++$callCountB;

            array_unshift($arguments, 'B');

            return $arguments;
        };

        $this->callsC = array();
        $callsC = &$this->callsC;
        $this->callCountC = 0;
        $callCountC = &$this->callCountC;
        $this->callbackC = function () use (&$callsC, &$callCountC) {
            $arguments = func_get_args();
            $callsC[] = $arguments;
            ++$callCountC;

            array_unshift($arguments, 'C');

            return $arguments;
        };

        $this->callsD = array();
        $callsD = &$this->callsD;
        $this->callCountD = 0;
        $callCountD = &$this->callCountD;
        $this->callbackD = function () use (&$callsD, &$callCountD) {
            $arguments = func_get_args();
            $callsD[] = $arguments;
            ++$callCountD;

            array_unshift($arguments, 'D');

            return $arguments;
        };

        $this->callsE = array();
        $callsE = &$this->callsE;
        $this->callCountE = 0;
        $callCountE = &$this->callCountE;
        $this->callbackE = function () use (&$callsE, &$callCountE) {
            $arguments = func_get_args();
            $callsE[] = $arguments;
            ++$callCountE;

            array_unshift($arguments, 'E');

            return $arguments;
        };

        $this->callsF = array();
        $callsF = &$this->callsF;
        $this->callCountF = 0;
        $callCountF = &$this->callCountF;
        $this->callbackF = function () use (&$callsF, &$callCountF) {
            $arguments = func_get_args();
            $callsF[] = $arguments;
            ++$callCountF;

            array_unshift($arguments, 'F');

            return $arguments;
        };

        $this->referenceCallback = function (&$a, &$b = null, &$c = null, &$d = null) {
            $a = 'a';
            $b = 'b';
            $c = 'c';
            $d = 'd';
        };
    }

    public function testGenerates()
    {
        $builder = $this->subject->generates(array('a' => 'b', 'c'));
        $generator = call_user_func($this->subject);
        $actual = iterator_to_array($generator);

        $this->assertInstanceOf('Eloquent\Phony\Stub\Answer\Builder\GeneratorAnswerBuilderInterface', $builder);
        $this->assertInstanceOf('Generator', $generator);
        $this->assertSame($this->subject, $builder->returns());
        $this->assertSame(array('a' => 'b', 0 => 'c'), $actual);
    }

    public function testGeneratesWithMultipleArguments()
    {
        $builder = $this->subject->generates(array('a'), array('b'));
        $actualA = iterator_to_array(call_user_func($this->subject));
        $actualB = iterator_to_array(call_user_func($this->subject));

        $this->assertInstanceOf('Eloquent\Phony\Stub\Answer\Builder\GeneratorAnswerBuilderInterface', $builder);
        $this->assertSame(array('a'), $actualA);
        $this->assertSame(array('b'), $actualB);
    }
}
