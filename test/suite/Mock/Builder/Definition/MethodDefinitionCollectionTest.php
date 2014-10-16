<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Builder\Definition;

use PHPUnit_Framework_TestCase;
use ReflectionMethod;

class MethodDefinitionCollectionTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->methods = array(
            'methodA' => new CustomMethodDefinition(true, 'methodA'),
            'testClassAStaticMethodA' => new RealMethodDefinition(
                new ReflectionMethod('Eloquent\Phony\Test\TestClassA::testClassAStaticMethodA')
            ),
            'testClassAStaticMethodB' => new RealMethodDefinition(
                new ReflectionMethod('Eloquent\Phony\Test\TestClassA::testClassAStaticMethodB')
            ),
            'methodB' => new CustomMethodDefinition(false, 'methodB'),
            'testClassAMethodA' => new RealMethodDefinition(
                new ReflectionMethod('Eloquent\Phony\Test\TestClassA::testClassAMethodA')
            ),
            'testClassAMethodB' => new RealMethodDefinition(
                new ReflectionMethod('Eloquent\Phony\Test\TestClassA::testClassAMethodB')
            ),
            'testClassAStaticMethodC' => new RealMethodDefinition(
                new ReflectionMethod('Eloquent\Phony\Test\TestClassA::testClassAStaticMethodC')
            ),
            'testClassAStaticMethodD' => new RealMethodDefinition(
                new ReflectionMethod('Eloquent\Phony\Test\TestClassA::testClassAStaticMethodD')
            ),
            'testClassAMethodC' => new RealMethodDefinition(
                new ReflectionMethod('Eloquent\Phony\Test\TestClassA::testClassAMethodC')
            ),
            'testClassAMethodD' => new RealMethodDefinition(
                new ReflectionMethod('Eloquent\Phony\Test\TestClassA::testClassAMethodD')
            ),

        );
        $this->subject = new MethodDefinitionCollection($this->methods);
    }

    public function testConstructor()
    {
        $this->assertSame($this->methods, $this->subject->methods());
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
}
