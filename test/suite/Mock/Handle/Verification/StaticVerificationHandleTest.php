<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Handle\Verification;

use Eloquent\Phony\Assertion\Recorder\AssertionRecorder;
use Eloquent\Phony\Assertion\Renderer\AssertionRenderer;
use Eloquent\Phony\Event\EventCollection;
use Eloquent\Phony\Feature\FeatureDetector;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Mock\Builder\Factory\MockBuilderFactory;
use Eloquent\Phony\Mock\Builder\MockBuilder;
use Eloquent\Phony\Stub\Factory\StubFactory;
use Eloquent\Phony\Stub\Factory\StubVerifierFactory;
use PHPUnit_Framework_TestCase;
use ReflectionProperty;

class StaticVerificationHandleTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->state = (object) array(
            'stubs' => (object) array(),
            'defaultAnswerCallback' => 'Eloquent\Phony\Stub\Stub::returnsEmptyAnswerCallback',
            'isRecording' => true,
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
        $this->subject = new StaticVerificationHandle(
            $this->class,
            $this->state,
            $this->stubFactory,
            $this->stubVerifierFactory,
            $this->assertionRenderer,
            $this->assertionRecorder,
            $this->invoker
        );

        $this->className = $this->class->getName();

        $handleProperty = $this->class->getProperty('_staticHandle');
        $handleProperty->setAccessible(true);
        $handle = $handleProperty->getValue(null);

        $stateProperty = new ReflectionProperty('Eloquent\Phony\Mock\Handle\AbstractHandle', 'state');
        $stateProperty->setAccessible(true);
        $stateProperty->setValue($handle, $this->state);
    }

    public function testFull()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassB');
        $className = $this->className;

        $this->assertSame($this->subject, $this->subject->full());
        $this->assertNull($className::testClassAStaticMethodA());
        $this->assertNull($className::testClassAStaticMethodB('a', 'b'));
    }

    public function testPartial()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassB');
        $className = $this->className;

        $this->assertSame($this->subject, $this->subject->partial());
        $this->assertSame('', $className::testClassAStaticMethodA());
        $this->assertSame('ab', $className::testClassAStaticMethodB('a', 'b'));
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
        $actual = $this->subject->stub('testClassAStaticMethodA');

        $this->assertInstanceOf('Eloquent\Phony\Stub\StubVerifier', $actual);
        $this->assertSame($actual, $this->subject->stub('testClassAStaticMethodA'));
        $this->assertSame($actual, $this->subject->state()->stubs->testclassastaticmethoda);
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
        $actual = $this->subject->testClassAStaticMethodA;

        $this->assertInstanceOf('Eloquent\Phony\Stub\StubVerifier', $actual);
        $this->assertSame($actual, $this->subject->testClassAStaticMethodA);
        $this->assertSame($actual, $this->subject->state()->stubs->testclassastaticmethoda);
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
        $actual = $this->subject->spy('testClassAStaticMethodA');

        $this->assertInstanceOf('Eloquent\Phony\Spy\Spy', $actual);
        $this->assertSame($actual, $this->subject->spy('testClassAStaticMethodA'));
        $this->assertSame($actual, $this->subject->state()->stubs->testclassastaticmethoda->spy());
    }

    public function testCheckNoInteraction()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassA');
        $className = $this->subject->className();

        $this->assertTrue((boolean) $this->subject->checkNoInteraction());

        $className::testClassAStaticMethodA();

        $this->assertFalse((boolean) $this->subject->checkNoInteraction());
    }

    public function testNoInteraction()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassA');

        $this->assertEquals(new EventCollection(array()), $this->subject->noInteraction());
    }

    public function testNoInteractionFailure()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassA', 'PhonyMockStaticVerificationNoInteraction');
        $className = $this->subject->className();
        $className::testClassAStaticMethodA('a', 'b');
        $className::testClassAStaticMethodB('c', 'd');
        $className::testClassAStaticMethodA('e', 'f');
        $expected = <<<'EOD'
Expected no interaction with TestClassA[static]. Calls:
    - TestClassA::testClassAStaticMethodA("a", "b")
    - TestClassA::testClassAStaticMethodB("c", "d")
    - TestClassA::testClassAStaticMethodA("e", "f")
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->noInteraction();
    }

    public function testMagicCall()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassA');
        $className = $this->className;
        $className::testClassAStaticMethodA();

        $this->assertSame($this->subject, $this->subject->testClassAStaticMethodA());
    }

    public function testMagicCallFailure()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassA');

        $this->setExpectedException('Eloquent\Phony\Mock\Exception\UndefinedMethodStubException');
        $this->subject->nonexistent();
    }

    public function testVerificationWithParentMethod()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassA');
        $this->subject->partial();
        $className = $this->className;
        $className::testClassAStaticMethodA('a', 'b');

        $this->assertSame($this->subject, $this->subject->testClassAStaticMethodA('a', 'b'));

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->testClassAStaticMethodA();
    }

    public function testVerificationWithTraitMethod()
    {
        if (!$this->featureDetector->isSupported('trait')) {
            $this->markTestSkipped('Requires traits.');
        }

        $this->setUpWith('Eloquent\Phony\Test\TestTraitA');
        $this->subject->partial();
        $className = $this->className;
        $a = 'a';
        $className::testClassAStaticMethodA($a, 'b');

        $this->assertSame($this->subject, $this->subject->testClassAStaticMethodA('a', 'b'));

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->testClassAStaticMethodA();
    }

    public function testVerificationWithMagicMethod()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassB');
        $this->subject->partial();
        $className = $this->className;
        $className::nonexistent('a', 'b');

        $this->assertSame($this->subject, $this->subject->nonexistent('a', 'b'));

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->nonexistent();
    }

    public function testVerificationWithNoParentMethod()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestInterfaceA');
        $this->subject->partial();
        $className = $this->className;
        $className::testClassAStaticMethodA('a', 'b');

        $this->assertSame($this->subject, $this->subject->testClassAStaticMethodA('a', 'b'));

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->testClassAStaticMethodA();
    }

    public function testVerificationFailureWithFinalMethod()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassF');
        $this->subject->partial();

        $this->setExpectedException('Eloquent\Phony\Mock\Exception\FinalMethodStubException');
        $this->subject->testClassFStaticMethodA;
    }

    public function testVerificationWithTraitFinalMethod()
    {
        if (!$this->featureDetector->isSupported('trait')) {
            $this->markTestSkipped('Requires traits.');
        }

        $this->setUpWith('Eloquent\Phony\Test\TestTraitG');
        $this->subject->partial();
        $className = $this->className;
        $className::testTraitGStaticMethodA('a', 'b');

        $this->assertSame($this->subject, $this->subject->testTraitGStaticMethodA('a', 'b'));

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->testTraitGStaticMethodA();
    }

    public function testVerificationWithCustomMethod()
    {
        $this->mockBuilder = $this->mockBuilderFactory->create(
            array(
                'static methodA' => function () {
                    return implode(func_get_args());
                },
            )
        );
        $this->class = $this->mockBuilder->build(true);
        $className = $this->class->getName();
        $this->subject = new StaticVerificationHandle(
            $this->class,
            $this->state,
            $this->stubFactory,
            $this->stubVerifierFactory,
            $this->assertionRenderer,
            $this->assertionRecorder,
            $this->invoker
        );
        $handleProperty = $this->class->getProperty('_staticHandle');
        $handleProperty->setAccessible(true);
        $handle = $handleProperty->getValue(null);
        $stateProperty = new ReflectionProperty('Eloquent\Phony\Mock\Handle\AbstractHandle', 'state');
        $stateProperty->setAccessible(true);
        $stateProperty->setValue($handle, $this->subject->state());
        $className::methodA('a', 'b');

        $this->assertSame($this->subject, $this->subject->methodA('a', 'b'));

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->methodA();
    }

    public function testStopRecording()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestInterfaceA');
        $className = $this->class->getName();

        $className::testClassAStaticMethodA('a', 'b');

        $this->assertSame($this->subject, $this->subject->stopRecording());

        $className::testClassAStaticMethodB('a', 'b');

        $this->subject->testClassAStaticMethodA->called();
        $this->subject->testClassAStaticMethodB->never()->called();
    }

    public function testStartRecording()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestInterfaceA');
        $className = $this->class->getName();

        $className::testClassAStaticMethodA('a', 'b');

        $this->assertSame($this->subject, $this->subject->stopRecording());
        $this->assertSame($this->subject, $this->subject->startRecording());

        $className::testClassAStaticMethodB('a', 'b');

        $this->subject->testClassAStaticMethodA->called();
        $this->subject->testClassAStaticMethodB->called();
    }
}
