<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Builder\Method;

use AllowDynamicProperties;
use Eloquent\Phony\Test\TestClassA;
use PHPUnit\Framework\TestCase;
use ReflectionFunction;
use ReflectionMethod;

#[AllowDynamicProperties]
class MethodDefinitionCollectionTest extends TestCase
{
    protected function setUp(): void
    {
        $this->callbackA = function () {};
        $this->callbackB = function () {};
        $this->methods = [
            'methodA' =>
                new CustomMethodDefinition(true, 'methodA', $this->callbackA, new ReflectionFunction($this->callbackA)),
            'methodB' =>
                new CustomMethodDefinition(false, 'methodB', $this->callbackB, new ReflectionFunction($this->callbackB)),
            'testClassAMethodA' => new RealMethodDefinition(
                new ReflectionMethod(TestClassA::class . '::testClassAMethodA'),
                'testClassAMethodA'
            ),
            'testClassAMethodB' => new RealMethodDefinition(
                new ReflectionMethod(TestClassA::class . '::testClassAMethodB'),
                'testClassAMethodB'
            ),
            'testClassAMethodC' => new RealMethodDefinition(
                new ReflectionMethod(TestClassA::class . '::testClassAMethodC'),
                'testClassAMethodC'
            ),
            'testClassAMethodD' => new RealMethodDefinition(
                new ReflectionMethod(TestClassA::class . '::testClassAMethodD'),
                'testClassAMethodD'
            ),
            'testClassAStaticMethodA' => new RealMethodDefinition(
                new ReflectionMethod(TestClassA::class . '::testClassAStaticMethodA'),
                'testClassAStaticMethodA'
            ),
            'testClassAStaticMethodB' => new RealMethodDefinition(
                new ReflectionMethod(TestClassA::class . '::testClassAStaticMethodB'),
                'testClassAStaticMethodB'
            ),
            'testClassAStaticMethodC' => new RealMethodDefinition(
                new ReflectionMethod(TestClassA::class . '::testClassAStaticMethodC'),
                'testClassAStaticMethodC'
            ),
            'testClassAStaticMethodD' => new RealMethodDefinition(
                new ReflectionMethod(TestClassA::class . '::testClassAStaticMethodD'),
                'testClassAStaticMethodD'
            ),
        ];
        $this->traitMethods = [
            new TraitMethodDefinition(
                new ReflectionMethod(TestClassA::class . '::testClassAMethodA'),
                'testClassAMethodA'
            ),
            new TraitMethodDefinition(
                new ReflectionMethod(TestClassA::class . '::testClassAMethodB'),
                'testClassAMethodB'
            ),
        ];
        $this->subject = new MethodDefinitionCollection($this->methods, $this->traitMethods);
    }

    public function testConstructor()
    {
        $this->assertSame($this->methods, $this->subject->allMethods());
        $this->assertSame($this->traitMethods, $this->subject->traitMethods());
        $this->assertSame(
            [
                'methodA' => $this->methods['methodA'],
                'testClassAStaticMethodA' => $this->methods['testClassAStaticMethodA'],
                'testClassAStaticMethodB' => $this->methods['testClassAStaticMethodB'],
                'testClassAStaticMethodC' => $this->methods['testClassAStaticMethodC'],
                'testClassAStaticMethodD' => $this->methods['testClassAStaticMethodD'],
            ],
            $this->subject->staticMethods()
        );
        $this->assertSame(
            [
                'methodB' => $this->methods['methodB'],
                'testClassAMethodA' => $this->methods['testClassAMethodA'],
                'testClassAMethodB' => $this->methods['testClassAMethodB'],
                'testClassAMethodC' => $this->methods['testClassAMethodC'],
                'testClassAMethodD' => $this->methods['testClassAMethodD'],
            ],
            $this->subject->methods()
        );
        $this->assertSame(
            [
                'methodA' => $this->methods['methodA'],
                'testClassAStaticMethodA' => $this->methods['testClassAStaticMethodA'],
                'testClassAStaticMethodB' => $this->methods['testClassAStaticMethodB'],
            ],
            $this->subject->publicStaticMethods()
        );
        $this->assertSame(
            [
                'methodB' => $this->methods['methodB'],
                'testClassAMethodA' => $this->methods['testClassAMethodA'],
                'testClassAMethodB' => $this->methods['testClassAMethodB'],
            ],
            $this->subject->publicMethods()
        );
        $this->assertSame(
            [
                'testClassAStaticMethodC' => $this->methods['testClassAStaticMethodC'],
                'testClassAStaticMethodD' => $this->methods['testClassAStaticMethodD'],
            ],
            $this->subject->protectedStaticMethods()
        );
        $this->assertSame(
            [
                'testClassAMethodC' => $this->methods['testClassAMethodC'],
                'testClassAMethodD' => $this->methods['testClassAMethodD'],
            ],
            $this->subject->protectedMethods()
        );
    }

    public function testMethodName()
    {
        $this->assertSame('methodA', $this->subject->methodName('methodA'));
        $this->assertSame('methodA', $this->subject->methodName('methoda'));
        $this->assertSame('methodA', $this->subject->methodName('METHODA'));
        $this->assertSame('', $this->subject->methodName('nonexistent'));
    }
}
