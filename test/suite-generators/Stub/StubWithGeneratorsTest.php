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

use Eloquent\Phony\Feature\FeatureDetector;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Stub\Answer\Builder\Factory\GeneratorAnswerBuilderFactory;
use PHPUnit_Framework_TestCase;

class StubWithGeneratorsTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->callback = 'implode';
        $this->self = (object) array();
        $this->label = 'label';
        $this->defaultAnswerCallback = function ($stub) {
            $stub->returns('default answer');
        };
        $this->matcherFactory = new MatcherFactory();
        $this->matcherVerifier = new MatcherVerifier();
        $this->invoker = new Invoker();
        $this->invocableInspector = new InvocableInspector();
        $this->generatorAnswerBuilderFactory = new GeneratorAnswerBuilderFactory();
        $this->subject = new Stub(
            $this->callback,
            $this->self,
            $this->label,
            $this->defaultAnswerCallback,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->invoker,
            $this->invocableInspector,
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

        $this->featureDetector = FeatureDetector::instance();
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

        $generator = call_user_func($this->subject);
        $actual = iterator_to_array($generator);

        $this->assertInstanceOf('Generator', $generator);
        $this->assertSame($this->subject, $builder->returns());
        $this->assertSame(array('a' => 'b', 0 => 'c'), $actual);
    }

    public function testGeneratesWithMultipleArguments()
    {
        $builder = $this->subject->generates(array('a'), array('b'));
        $actualA = iterator_to_array(call_user_func($this->subject));
        $actualB = iterator_to_array(call_user_func($this->subject));
        $actualC = iterator_to_array(call_user_func($this->subject));

        $this->assertInstanceOf('Eloquent\Phony\Stub\Answer\Builder\GeneratorAnswerBuilderInterface', $builder);
        $this->assertSame(array('a'), $actualA);
        $this->assertSame(array('b'), $actualB);
        $this->assertSame(array('b'), $actualC);
    }
}
