<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Builder\Method;

use PHPUnit_Framework_TestCase;
use ReflectionFunction;
use ReflectionMethod;

class MethodDefinitionCollectionTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->callbackA = function () {};
        $this->callbackB = function () {};
        $this->methods = array(
            'methodA' =>
                new CustomMethodDefinition(true, 'methodA', $this->callbackA, new ReflectionFunction($this->callbackA)),
            'methodB' =>
                new CustomMethodDefinition(false, 'methodB', $this->callbackB, new ReflectionFunction($this->callbackB)),
            'testClassAMethodA' => new RealMethodDefinition(
                new ReflectionMethod('Eloquent\Phony\Test\TestClassA::testClassAMethodA'),
                'testClassAMethodA'
            ),
            'testClassAMethodB' => new RealMethodDefinition(
                new ReflectionMethod('Eloquent\Phony\Test\TestClassA::testClassAMethodB'),
                'testClassAMethodB'
            ),
            'testClassAMethodC' => new RealMethodDefinition(
                new ReflectionMethod('Eloquent\Phony\Test\TestClassA::testClassAMethodC'),
                'testClassAMethodC'
            ),
            'testClassAMethodD' => new RealMethodDefinition(
                new ReflectionMethod('Eloquent\Phony\Test\TestClassA::testClassAMethodD'),
                'testClassAMethodD'
            ),
            'testClassAStaticMethodA' => new RealMethodDefinition(
                new ReflectionMethod('Eloquent\Phony\Test\TestClassA::testClassAStaticMethodA'),
                'testClassAStaticMethodA'
            ),
            'testClassAStaticMethodB' => new RealMethodDefinition(
                new ReflectionMethod('Eloquent\Phony\Test\TestClassA::testClassAStaticMethodB'),
                'testClassAStaticMethodB'
            ),
            'testClassAStaticMethodC' => new RealMethodDefinition(
                new ReflectionMethod('Eloquent\Phony\Test\TestClassA::testClassAStaticMethodC'),
                'testClassAStaticMethodC'
            ),
            'testClassAStaticMethodD' => new RealMethodDefinition(
                new ReflectionMethod('Eloquent\Phony\Test\TestClassA::testClassAStaticMethodD'),
                'testClassAStaticMethodD'
            ),
        );
        $this->traitMethods = array(
            new TraitMethodDefinition(
                new ReflectionMethod('Eloquent\Phony\Test\TestClassA::testClassAMethodA'),
                'testClassAMethodA'
            ),
            new TraitMethodDefinition(
                new ReflectionMethod('Eloquent\Phony\Test\TestClassA::testClassAMethodB'),
                'testClassAMethodB'
            ),
        );
        $this->subject = new MethodDefinitionCollection($this->methods, $this->traitMethods);
    }

    public function testConstructor()
    {
        $this->assertSame($this->methods, $this->subject->allMethods());
        $this->assertSame($this->traitMethods, $this->subject->traitMethods());
        $this->assertSame(
            array(
                'methodA' => $this->methods['methodA'],
                'testClassAStaticMethodA' => $this->methods['testClassAStaticMethodA'],
                'testClassAStaticMethodB' => $this->methods['testClassAStaticMethodB'],
                'testClassAStaticMethodC' => $this->methods['testClassAStaticMethodC'],
                'testClassAStaticMethodD' => $this->methods['testClassAStaticMethodD'],
            ),
            $this->subject->staticMethods()
        );
        $this->assertSame(
            array(
                'methodB' => $this->methods['methodB'],
                'testClassAMethodA' => $this->methods['testClassAMethodA'],
                'testClassAMethodB' => $this->methods['testClassAMethodB'],
                'testClassAMethodC' => $this->methods['testClassAMethodC'],
                'testClassAMethodD' => $this->methods['testClassAMethodD'],
            ),
            $this->subject->methods()
        );
        $this->assertSame(
            array(
                'methodA' => $this->methods['methodA'],
                'testClassAStaticMethodA' => $this->methods['testClassAStaticMethodA'],
                'testClassAStaticMethodB' => $this->methods['testClassAStaticMethodB'],
            ),
            $this->subject->publicStaticMethods()
        );
        $this->assertSame(
            array(
                'methodB' => $this->methods['methodB'],
                'testClassAMethodA' => $this->methods['testClassAMethodA'],
                'testClassAMethodB' => $this->methods['testClassAMethodB'],
            ),
            $this->subject->publicMethods()
        );
        $this->assertSame(
            array(
                'testClassAStaticMethodC' => $this->methods['testClassAStaticMethodC'],
                'testClassAStaticMethodD' => $this->methods['testClassAStaticMethodD'],
            ),
            $this->subject->protectedStaticMethods()
        );
        $this->assertSame(
            array(
                'testClassAMethodC' => $this->methods['testClassAMethodC'],
                'testClassAMethodD' => $this->methods['testClassAMethodD'],
            ),
            $this->subject->protectedMethods()
        );
    }

    public function testMethodName()
    {
        $this->assertSame('methodA', $this->subject->methodName('methodA'));
        $this->assertSame('methodA', $this->subject->methodName('methoda'));
        $this->assertSame('methodA', $this->subject->methodName('METHODA'));
        $this->assertNull($this->subject->methodName('nonexistent'));
    }
}
