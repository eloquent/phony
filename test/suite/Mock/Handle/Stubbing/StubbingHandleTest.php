<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Handle\Stubbing;

use Eloquent\Phony\Assertion\Recorder\AssertionRecorder;
use Eloquent\Phony\Assertion\Renderer\AssertionRenderer;
use Eloquent\Phony\Event\EventCollection;
use Eloquent\Phony\Feature\FeatureDetector;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Mock\Builder\Factory\MockBuilderFactory;
use Eloquent\Phony\Mock\Builder\MockBuilder;
use Eloquent\Phony\Stub\Factory\StubFactory;
use Eloquent\Phony\Stub\Factory\StubVerifierFactory;
use Eloquent\Phony\Test\TestClassH;
use PHPUnit_Framework_TestCase;
use ReflectionMethod;

class StubbingHandleTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->state = (object) array(
            'stubs' => (object) array(),
            'defaultAnswerCallback' => 'Eloquent\Phony\Stub\Stub::returnsEmptyAnswerCallback',
            'isRecording' => true,
            'label' => 'label',
        );
        $this->stubFactory = StubFactory::instance();
        $this->stubVerifierFactory = StubVerifierFactory::instance();
        $this->assertionRenderer = AssertionRenderer::instance();
        $this->assertionRecorder = AssertionRecorder::instance();
        $this->invoker = new Invoker();

        $this->mockBuilderFactory = MockBuilderFactory::instance();
        $this->featureDetector = FeatureDetector::instance();
    }

    protected function setUpWith($className, $mockClassName = null)
    {
        $this->mockBuilder = $this->mockBuilderFactory->create($className);
        $this->mockBuilder->named($mockClassName);
        $this->class = $this->mockBuilder->build(true);
        $this->mock = $this->mockBuilder->partial();
        $this->subject = new StubbingHandle(
            $this->mock,
            $this->state,
            $this->stubFactory,
            $this->stubVerifierFactory,
            $this->assertionRenderer,
            $this->assertionRecorder,
            $this->invoker
        );

        $this->className = $this->class->getName();

        $handleProperty = $this->class->getProperty('_handle');
        $handleProperty->setAccessible(true);
        $handleProperty->setValue($this->mock, $this->subject);
    }

    public function testSetLabel()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassA');

        $this->assertSame($this->subject, $this->subject->setLabel(null));
        $this->assertNull($this->subject->label());
        $this->assertSame($this->subject, $this->subject->setLabel($this->state->label));
        $this->assertSame($this->state->label, $this->subject->label());
    }

    public function testSetIsAdaptable()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassA');

        $this->assertTrue($this->subject->isAdaptable());
        $this->assertSame($this->subject, $this->subject->setIsAdaptable(false));
        $this->assertFalse($this->subject->isAdaptable());
    }

    public function testFull()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassB');

        $this->assertSame($this->subject, $this->subject->full());
        $this->assertNull($this->mock->testClassAMethodA());
        $this->assertNull($this->mock->testClassAMethodB('a', 'b'));
    }

    public function testPartial()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassB');

        $this->assertSame($this->subject, $this->subject->partial());
        $this->assertSame('', $this->mock->testClassAMethodA());
        $this->assertSame('ab', $this->mock->testClassAMethodB('a', 'b'));
    }

    public function testProxy()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassA');

        $protectedMethod = new ReflectionMethod($this->mock, 'testClassAMethodC');
        $protectedMethod->setAccessible(true);
        $privateMethod = new ReflectionMethod($this->mock, 'testClassAMethodE');
        $privateMethod->setAccessible(true);

        $this->assertSame($this->subject, $this->subject->proxy(new TestClassH()));
        $this->assertSame('final ab', $this->mock->testClassAMethodB('a', 'b'));
        $this->assertSame('final protected ab', $protectedMethod->invoke($this->mock, 'a', 'b'));
        $this->assertSame('private ab', $privateMethod->invoke($this->mock, 'a', 'b'));
    }

    public function testSetDefaultAnswerCallback()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassA');
        $callbackA = function () {};
        $callbackB = function () {};

        $this->assertSame($this->subject, $this->subject->setDefaultAnswerCallback($callbackA));
        $this->assertSame($callbackA, $this->subject->defaultAnswerCallback());
        $this->assertSame($this->subject, $this->subject->setDefaultAnswerCallback($callbackB));
        $this->assertSame($callbackB, $this->subject->defaultAnswerCallback());
    }

    public function testStub()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassA');
        $actual = $this->subject->stub('testClassAMethodA');

        $this->assertInstanceOf('Eloquent\Phony\Stub\StubVerifier', $actual);
        $this->assertSame($actual, $this->subject->stub('testClassAMethodA'));
        $this->assertSame($actual, $this->subject->state()->stubs->testclassamethoda);
    }

    public function testStubWithMagic()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassB');
        $actual = $this->subject->stub('nonexistent');

        $this->assertInstanceOf('Eloquent\Phony\Stub\StubVerifier', $actual);
        $this->assertSame($actual, $this->subject->stub('nonexistent'));
        $this->assertSame($actual, $this->subject->state()->stubs->nonexistent);
    }

    public function testStubFailure()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassA');

        $this->setExpectedException('Eloquent\Phony\Mock\Exception\UndefinedMethodStubException');
        $this->subject->stub('nonexistent');
    }

    public function testMagicProperty()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassA');
        $actual = $this->subject->testClassAMethodA;

        $this->assertInstanceOf('Eloquent\Phony\Stub\StubVerifier', $actual);
        $this->assertSame($actual, $this->subject->testClassAMethodA);
        $this->assertSame($actual, $this->subject->state()->stubs->testclassamethoda);
    }

    public function testMagicPropertyFailure()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassA');

        $this->setExpectedException('Eloquent\Phony\Mock\Exception\UndefinedMethodStubException');
        $this->subject->nonexistent;
    }

    public function testSpy()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassA');
        $actual = $this->subject->spy('testClassAMethodA');

        $this->assertInstanceOf('Eloquent\Phony\Spy\Spy', $actual);
        $this->assertSame($actual, $this->subject->spy('testClassAMethodA'));
        $this->assertSame($actual, $this->subject->state()->stubs->testclassamethoda->spy());
    }

    public function testCheckNoInteraction()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassA');

        $this->assertTrue((boolean) $this->subject->checkNoInteraction());

        $this->mock->testClassAMethodA();

        $this->assertFalse((boolean) $this->subject->checkNoInteraction());
    }

    public function testNoInteraction()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassA');

        $this->assertEquals(new EventCollection(array()), $this->subject->noInteraction());
    }

    public function testNoInteractionFailure()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassA', 'PhonyMockStubbingNoInteraction');
        $this->mock->testClassAMethodA('a', 'b');
        $this->mock->testClassAMethodB('c', 'd');
        $this->mock->testClassAMethodA('e', 'f');
        $expected = <<<'EOD'
Expected no interaction with TestClassA[label]. Calls:
    - TestClassA[label]->testClassAMethodA("a", "b")
    - TestClassA[label]->testClassAMethodB("c", "d")
    - TestClassA[label]->testClassAMethodA("e", "f")
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->noInteraction();
    }

    public function testMagicCall()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassA');
        $actual = $this->subject->testClassAMethodA();
        $actual->returns();

        $this->assertInstanceOf('Eloquent\Phony\Stub\StubVerifier', $actual);
        $this->assertSame($actual, $this->subject->testClassAMethodA);
        $this->assertSame($actual, $this->subject->state()->stubs->testclassamethoda);
        $this->assertSame($actual, $this->subject->testClassAMethodA()->returns());
    }

    public function testMagicCallFailure()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassA');

        $this->setExpectedException('Eloquent\Phony\Mock\Exception\UndefinedMethodStubException');
        $this->subject->nonexistent();
    }

    public function testConstruct()
    {
        $this->mockBuilder = $this->mockBuilderFactory->create('Eloquent\Phony\Test\TestClassB');
        $this->class = $this->mockBuilder->build(true);
        $this->mock = $this->mockBuilder->partialWith(null);
        $this->subject = new StubbingHandle(
            $this->mock,
            $this->state,
            $this->stubFactory,
            $this->stubVerifierFactory,
            $this->assertionRenderer,
            $this->assertionRecorder,
            $this->invoker
        );

        $this->assertNull($this->mock->constructorArguments);
        $this->assertSame($this->subject, $this->subject->construct('a', 'b'));
        $this->assertSame(array('a', 'b'), $this->mock->constructorArguments);
    }

    public function testConstructWith()
    {
        $this->mockBuilder = $this->mockBuilderFactory->create('Eloquent\Phony\Test\TestClassB');
        $this->class = $this->mockBuilder->build(true);
        $this->mock = $this->mockBuilder->partialWith(null);
        $this->subject = new StubbingHandle(
            $this->mock,
            $this->state,
            $this->stubFactory,
            $this->stubVerifierFactory,
            $this->assertionRenderer,
            $this->assertionRecorder,
            $this->invoker
        );

        $this->assertNull($this->mock->constructorArguments);
        $this->assertSame($this->subject, $this->subject->constructWith(array('a', 'b')));
        $this->assertSame(array('a', 'b'), $this->mock->constructorArguments);
    }

    public function testConstructWithWithReferenceParameters()
    {
        $this->mockBuilder = $this->mockBuilderFactory->create('Eloquent\Phony\Test\TestClassA');
        $this->class = $this->mockBuilder->build(true);
        $this->mock = $this->mockBuilder->partialWith(null);
        $this->subject = new StubbingHandle(
            $this->mock,
            $this->state,
            $this->stubFactory,
            $this->stubVerifierFactory,
            $this->assertionRenderer,
            $this->assertionRecorder,
            $this->invoker
        );
        $a = 'a';
        $b = 'b';

        $this->assertNull($this->mock->constructorArguments);
        $this->assertSame($this->subject, $this->subject->constructWith(array(&$a, &$b)));
        $this->assertSame('first', $a);
        $this->assertSame('second', $b);
    }

    public function testStubbingWithParentMethod()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassA');
        $this->subject->partial();
        $this->subject->testClassAMethodA('a', 'b')->returns('x');

        $this->assertSame('x', $this->mock->testClassAMethodA('a', 'b'));
        $this->assertSame('cd', $this->mock->testClassAMethodA('c', 'd'));
    }

    public function testStubbingWithTraitMethod()
    {
        if (!$this->featureDetector->isSupported('trait')) {
            $this->markTestSkipped('Requires traits.');
        }

        $this->setUpWith('Eloquent\Phony\Test\TestTraitA');
        $this->subject->partial();
        $this->subject->testClassAMethodB('a', 'b')->returns('x');

        $this->assertSame('x', $this->mock->testClassAMethodB('a', 'b'));
        $this->assertSame('cd', $this->mock->testClassAMethodB('c', 'd'));
    }

    public function testStubbingWithMagicMethod()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassB');
        $this->subject->partial();
        $this->subject->nonexistent('a', 'b')->returns('x');

        $this->assertSame('x', $this->mock->nonexistent('a', 'b'));
        $this->assertSame('magic nonexistent cd', $this->mock->nonexistent('c', 'd'));
    }

    public function testStubbingWithNoParentMethod()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestInterfaceA');
        $this->subject->partial();
        $this->subject->testClassAMethodA('a', 'b')->returns('x');

        $this->assertSame('x', $this->mock->testClassAMethodA('a', 'b'));
        $this->assertNull($this->mock->testClassAMethodA('c', 'd'));
    }

    public function testStubbingFailureWithFinalMethod()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassF');
        $this->subject->partial();

        $this->setExpectedException('Eloquent\Phony\Mock\Exception\FinalMethodStubException');
        $this->subject->testClassFMethodA;
    }

    public function testStubbingWithTraitFinalMethod()
    {
        if (!$this->featureDetector->isSupported('trait')) {
            $this->markTestSkipped('Requires traits.');
        }

        $this->setUpWith('Eloquent\Phony\Test\TestTraitG');
        $this->subject->partial();
        $this->subject->testTraitGMethodA('a', 'b')->returns('x');

        $this->assertSame('x', $this->mock->testTraitGMethodA('a', 'b'));
        $this->assertSame('cd', $this->mock->testTraitGMethodA('c', 'd'));
    }

    public function testStubbingWithCustomMethod()
    {
        $this->mockBuilder = $this->mockBuilderFactory->create(
            array(
                'methodA' => function () {
                    return implode(func_get_args());
                },
            )
        );
        $this->class = $this->mockBuilder->build(true);
        $this->mock = $this->mockBuilder->partial();
        $this->subject = new StubbingHandle(
            $this->mock,
            $this->state,
            $this->stubFactory,
            $this->stubVerifierFactory,
            $this->assertionRenderer,
            $this->assertionRecorder,
            $this->invoker
        );
        $handleProperty = $this->class->getProperty('_handle');
        $handleProperty->setAccessible(true);
        $handleProperty->setValue($this->mock, $this->subject);
        $this->subject->partial();
        $this->subject->methodA('a', 'b')->returns('x');

        $this->assertSame('x', $this->mock->methodA('a', 'b'));
        $this->assertSame('cd', $this->mock->methodA('c', 'd'));
    }

    public function testStopRecording()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestInterfaceA');

        $this->mock->testClassAMethodA('a', 'b');

        $this->assertSame($this->subject, $this->subject->stopRecording());

        $this->mock->testClassAMethodB('a', 'b');

        $this->subject->testClassAMethodA->called();
        $this->subject->testClassAMethodB->never()->called();
    }

    public function testStartRecording()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestInterfaceA');

        $this->mock->testClassAMethodA('a', 'b');

        $this->assertSame($this->subject, $this->subject->stopRecording());
        $this->assertSame($this->subject, $this->subject->startRecording());

        $this->mock->testClassAMethodB('a', 'b');

        $this->subject->testClassAMethodA->called();
        $this->subject->testClassAMethodB->called();
    }
}
