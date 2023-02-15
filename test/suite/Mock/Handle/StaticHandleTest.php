<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Handle;

use AllowDynamicProperties;
use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Assertion\Exception\AssertionException;
use Eloquent\Phony\Assertion\ExceptionAssertionRecorder;
use Eloquent\Phony\Call\CallVerifierFactory;
use Eloquent\Phony\Difference\DifferenceEngine;
use Eloquent\Phony\Event\EventSequence;
use Eloquent\Phony\Exporter\InlineExporter;
use Eloquent\Phony\Hook\FunctionHookManager;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Matcher\MatcherFactory;
use Eloquent\Phony\Matcher\MatcherVerifier;
use Eloquent\Phony\Mock\Builder\MockBuilderFactory;
use Eloquent\Phony\Mock\Exception\FinalMethodStubException;
use Eloquent\Phony\Mock\Exception\UndefinedMethodStubException;
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Spy\GeneratorSpyMap;
use Eloquent\Phony\Spy\SpyData;
use Eloquent\Phony\Spy\SpyFactory;
use Eloquent\Phony\Stub\Answer\Builder\GeneratorAnswerBuilderFactory;
use Eloquent\Phony\Stub\EmptyValueFactory;
use Eloquent\Phony\Stub\StubData;
use Eloquent\Phony\Stub\StubFactory;
use Eloquent\Phony\Stub\StubVerifier;
use Eloquent\Phony\Stub\StubVerifierFactory;
use Eloquent\Phony\Test\TestClassA;
use Eloquent\Phony\Test\TestClassB;
use Eloquent\Phony\Test\TestClassF;
use Eloquent\Phony\Test\TestClassH;
use Eloquent\Phony\Test\TestInterfaceA;
use Eloquent\Phony\Test\TestTraitA;
use Eloquent\Phony\Test\TestTraitG;
use Eloquent\Phony\Verification\GeneratorVerifierFactory;
use Eloquent\Phony\Verification\IterableVerifierFactory;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

#[AllowDynamicProperties]
class StaticHandleTest extends TestCase
{
    protected function setUp(): void
    {
        $this->state = (object) [
            'stubs' => (object) [],
            'defaultAnswerCallback' => [StubData::class, 'returnsEmptyAnswerCallback'],
            'isRecording' => true,
        ];
        $this->stubFactory = StubFactory::instance();
        $this->idSequencer = new Sequencer();
        $this->invocableInspector = new InvocableInspector();
        $this->featureDetector = FeatureDetector::instance();
        $this->generatorSpyMap = GeneratorSpyMap::instance();
        $this->exporter = new InlineExporter(
            1,
            $this->idSequencer,
            $this->generatorSpyMap,
            $this->invocableInspector
        );
        $this->matcherVerifier = new MatcherVerifier();
        $this->differenceEngine = new DifferenceEngine($this->featureDetector);
        $this->differenceEngine->setUseColor(false);
        $this->assertionRenderer = new AssertionRenderer(
            $this->matcherVerifier,
            $this->exporter,
            $this->differenceEngine,
            $this->featureDetector
        );
        $this->assertionRenderer->setUseColor(false);
        $this->callVerifierFactory = CallVerifierFactory::instance();
        $this->assertionRecorder = ExceptionAssertionRecorder::instance();
        $this->assertionRecorder->setCallVerifierFactory($this->callVerifierFactory);
        $this->stubVerifierFactory = new StubVerifierFactory(
            $this->stubFactory,
            SpyFactory::instance(),
            MatcherFactory::instance(),
            $this->matcherVerifier,
            GeneratorVerifierFactory::instance(),
            IterableVerifierFactory::instance(),
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            GeneratorAnswerBuilderFactory::instance(),
            FunctionHookManager::instance()
        );
        $this->emptyValueFactory = EmptyValueFactory::instance();
        $this->invoker = new Invoker();

        $this->mockBuilderFactory = MockBuilderFactory::instance();
        $this->featureDetector = FeatureDetector::instance();
    }

    protected function setUpWith($className, $mockClassName = '')
    {
        $this->mockBuilder = $this->mockBuilderFactory->create($className);
        if ($mockClassName) {
            $this->mockBuilder->named($mockClassName);
        }
        $this->class = $this->mockBuilder->build(true);
        $this->subject = new StaticHandle(
            $this->class,
            $this->state,
            $this->stubFactory,
            $this->stubVerifierFactory,
            $this->emptyValueFactory,
            $this->assertionRenderer,
            $this->assertionRecorder,
            $this->invoker
        );

        $this->className = $this->class->getName();

        $handleProperty = $this->class->getProperty('_staticHandle');
        $handleProperty->setAccessible(true);
        $handleProperty->setValue(null, $this->subject);
    }

    public function testFull()
    {
        $this->setUpWith(TestClassB::class);
        $className = $this->className;

        $this->assertSame($this->subject, $this->subject->full());
        $this->assertNull($className::testClassAStaticMethodA());
        $this->assertNull($className::testClassAStaticMethodB('a', 'b'));
    }

    public function testPartial()
    {
        $this->setUpWith(TestClassB::class);
        $className = $this->className;

        $this->assertSame($this->subject, $this->subject->partial());
        $this->assertSame('', $className::testClassAStaticMethodA());
        $this->assertSame('ab', $className::testClassAStaticMethodB('a', 'b'));
    }

    public function testProxy()
    {
        $this->setUpWith(TestClassA::class);
        $className = $this->className;

        $protectedMethod = new ReflectionMethod($className, 'testClassAStaticMethodC');
        $protectedMethod->setAccessible(true);
        $privateMethod = new ReflectionMethod($className, 'testClassAStaticMethodE');
        $privateMethod->setAccessible(true);

        $this->assertSame($this->subject, $this->subject->proxy(new TestClassH()));
        $this->assertSame('final ab', $className::testClassAStaticMethodA('a', 'b'));
        $this->assertSame('final protected ab', $protectedMethod->invoke(null, 'a', 'b'));
        $this->assertSame('private ab', $privateMethod->invoke(null, 'a', 'b'));
    }

    public function testSetDefaultAnswerCallback()
    {
        $this->setUpWith(TestClassA::class);
        $callbackA = function () {};
        $callbackB = function () {};

        $this->assertSame($this->subject, $this->subject->setDefaultAnswerCallback($callbackA));
        $this->assertSame($callbackA, $this->subject->defaultAnswerCallback());
        $this->assertSame($this->subject, $this->subject->setDefaultAnswerCallback($callbackB));
        $this->assertSame($callbackB, $this->subject->defaultAnswerCallback());
    }

    public function testStub()
    {
        $this->setUpWith(TestClassA::class);
        $actual = $this->subject->stub('testClassAStaticMethodA');

        $this->assertInstanceOf(StubVerifier::class, $actual);
        $this->assertSame($actual, $this->subject->stub('testClassAStaticMethodA'));
        $this->assertSame($actual, $this->subject->state()->stubs->testclassastaticmethoda);
    }

    public function testStubWithMagic()
    {
        $this->setUpWith(TestClassB::class);
        $actual = $this->subject->stub('nonexistent');

        $this->assertInstanceOf(StubVerifier::class, $actual);
        $this->assertSame($actual, $this->subject->stub('nonexistent'));
        $this->assertSame($actual, $this->subject->state()->stubs->nonexistent);
    }

    public function testStubFailure()
    {
        $this->setUpWith(TestClassA::class);

        $this->expectException(UndefinedMethodStubException::class);
        $this->subject->stub('nonexistent');
    }

    public function testMagicProperty()
    {
        $this->setUpWith(TestClassA::class);
        $actual = $this->subject->testClassAStaticMethodA;

        $this->assertInstanceOf(StubVerifier::class, $actual);
        $this->assertSame($actual, $this->subject->testClassAStaticMethodA);
        $this->assertSame($actual, $this->subject->state()->stubs->testclassastaticmethoda);
    }

    public function testMagicPropertyFailure()
    {
        $this->setUpWith(TestClassA::class);

        $this->expectException(UndefinedMethodStubException::class);
        $this->subject->nonexistent;
    }

    public function testSpy()
    {
        $this->setUpWith(TestClassA::class);
        $actual = $this->subject->spy('testClassAStaticMethodA');

        $this->assertInstanceOf(SpyData::class, $actual);
        $this->assertSame($actual, $this->subject->spy('testClassAStaticMethodA'));
        $this->assertSame($actual, $this->subject->state()->stubs->testclassastaticmethoda->spy());
    }

    public function testCheckNoInteraction()
    {
        $this->setUpWith(TestClassA::class);
        $className = $this->subject->className();

        $this->assertTrue((bool) $this->subject->checkNoInteraction());

        $className::testClassAStaticMethodA();

        $this->assertFalse((bool) $this->subject->checkNoInteraction());
    }

    public function testNoInteraction()
    {
        $this->setUpWith(TestClassA::class);

        $this->assertEquals(new EventSequence([], $this->callVerifierFactory), $this->subject->noInteraction());
    }

    public function testNoInteractionFailure()
    {
        $this->setUpWith(TestClassA::class, 'PhonyMockStaticStubbingNoInteraction');
        $className = $this->subject->className();
        $className::testClassAStaticMethodA('a', 'b');
        $className::testClassAStaticMethodB('c', 'd');
        $className::testClassAStaticMethodA('e', 'f');

        $this->expectException(AssertionException::class);
        $this->subject->noInteraction();
    }

    public function testStubbingWithParentMethod()
    {
        $this->setUpWith(TestClassA::class);
        $this->subject->partial();
        $className = $this->className;
        $this->subject->testClassAStaticMethodA->with('a', 'b')->returns('x');

        $this->assertSame('x', $className::testClassAStaticMethodA('a', 'b'));
        $this->assertSame('cd', $className::testClassAStaticMethodA('c', 'd'));
    }

    public function testStubbingWithTraitMethod()
    {
        $this->setUpWith(TestTraitA::class);
        $this->subject->partial();
        $className = $this->className;
        $a = 'a';
        $c = 'c';
        $this->subject->testClassAStaticMethodA->with('a', 'b')->returns('x');

        $this->assertSame('x', $className::testClassAStaticMethodA($a, 'b'));
        $this->assertSame('cd', $className::testClassAStaticMethodA($c, 'd'));
    }

    public function testStubbingWithMagicMethod()
    {
        $this->setUpWith(TestClassB::class);
        $this->subject->partial();
        $className = $this->className;
        $this->subject->nonexistent->with('a', 'b')->returns('x');

        $this->assertSame('x', $className::nonexistent('a', 'b'));
        $this->assertSame('static magic nonexistent cd', $className::nonexistent('c', 'd'));
    }

    public function testStubbingWithNoParentMethod()
    {
        $this->setUpWith(TestInterfaceA::class);
        $this->subject->partial();
        $className = $this->className;
        $this->subject->testClassAStaticMethodA->with('a', 'b')->returns('x');

        $this->assertSame('x', $className::testClassAStaticMethodA('a', 'b'));
        $this->assertNull($className::testClassAStaticMethodA('c', 'd'));
    }

    public function testStubbingFailureWithFinalMethod()
    {
        $this->setUpWith(TestClassF::class);
        $this->subject->partial();

        $this->expectException(FinalMethodStubException::class);
        $this->subject->testClassFStaticMethodA;
    }

    public function testStubbingWithTraitFinalMethod()
    {
        $this->setUpWith(TestTraitG::class);
        $this->subject->partial();
        $className = $this->className;
        $this->subject->testTraitGStaticMethodA->with('a', 'b')->returns('x');

        $this->assertSame('x', $className::testTraitGStaticMethodA('a', 'b'));
        $this->assertSame('cd', $className::testTraitGStaticMethodA('c', 'd'));
    }

    public function testStubbingWithCustomMethod()
    {
        $this->mockBuilder = $this->mockBuilderFactory->create(
            [
                'static methodA' => function () {
                    return implode(func_get_args());
                },
            ]
        );
        $this->class = $this->mockBuilder->build(true);
        $className = $this->class->getName();
        $this->subject = new StaticHandle(
            $this->class,
            $this->state,
            $this->stubFactory,
            $this->stubVerifierFactory,
            $this->emptyValueFactory,
            $this->assertionRenderer,
            $this->assertionRecorder,
            $this->invoker
        );
        $this->subject->partial();
        $handleProperty = $this->class->getProperty('_staticHandle');
        $handleProperty->setAccessible(true);
        $handleProperty->setValue(null, $this->subject);
        $this->subject->methodA->with('a', 'b')->returns('x');

        $this->assertSame('x', $className::methodA('a', 'b'));
        $this->assertSame('cd', $className::methodA('c', 'd'));
    }

    public function testStopRecording()
    {
        $this->setUpWith(TestInterfaceA::class);
        $className = $this->class->getName();

        $className::testClassAStaticMethodA('a', 'b');

        $this->assertSame($this->subject, $this->subject->stopRecording());

        $className::testClassAStaticMethodB('a', 'b');

        $this->subject->testClassAStaticMethodA->called();
        $this->subject->testClassAStaticMethodB->never()->called();
    }

    public function testStartRecording()
    {
        $this->setUpWith(TestInterfaceA::class);
        $className = $this->class->getName();

        $className::testClassAStaticMethodA('a', 'b');

        $this->assertSame($this->subject, $this->subject->stopRecording());
        $this->assertSame($this->subject, $this->subject->startRecording());

        $className::testClassAStaticMethodB('a', 'b');

        $this->subject->testClassAStaticMethodA->called();
        $this->subject->testClassAStaticMethodB->called();
    }
}
