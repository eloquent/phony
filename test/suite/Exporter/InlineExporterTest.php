<?php

declare(strict_types=1);

namespace Eloquent\Phony\Exporter;

use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Mock\Builder\MockBuilderFactory;
use Eloquent\Phony\Phony;
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Spy\SpyFactory;
use Eloquent\Phony\Spy\SpyVerifierFactory;
use Eloquent\Phony\Stub\StubFactory;
use Eloquent\Phony\Stub\StubVerifierFactory;
use Eloquent\Phony\Test\Properties\TestBaseClass;
use Eloquent\Phony\Test\Properties\TestDerivedClassA;
use Eloquent\Phony\Test\TestClassA;
use Eloquent\Phony\Test\TestClassE;
use Eloquent\Phony\Test\TestInterfaceA;
use Eloquent\Phony\Test\TestTraitA;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;
use TestClass;
use WeakReference;

class InlineExporterTest extends TestCase
{
    protected function setUp(): void
    {
        $this->depth = -1;
        $this->objectSequencer = new Sequencer();
        $this->invocableInspector = InvocableInspector::instance();
        $this->subject = new InlineExporter($this->depth, $this->objectSequencer, $this->invocableInspector);

        $this->featureDetector = FeatureDetector::instance();
        $this->mockBuilderFactory = MockBuilderFactory::instance();
        $this->spyFactory = SpyFactory::instance();
        $this->stubFactory = StubFactory::instance();
        $this->spyVerifierFactory = SpyVerifierFactory::instance();
        $this->stubVerifierFactory = StubVerifierFactory::instance();
    }

    public function testSetDepth()
    {
        $this->assertSame($this->depth, $this->subject->setDepth(111));
        $this->assertSame(111, $this->subject->setDepth($this->depth));
    }

    public function exportData()
    {
        return [
            'null'             => [null,                                  'null'],
            'true'             => [true,                                  'true'],
            'false'            => [false,                                 'false'],
            '0'                => [0,                                     '0'],
            '-0'               => [-0,                                    '0'],
            '1'                => [1,                                     '1'],
            '-1'               => [-1,                                    '-1'],
            '0.0'              => [0.0,                                   '0.000000e+0'],
            '-0.0'             => [-0.0,                                  '0.000000e+0'],
            '1.0'              => [1.0,                                   '1.000000e+0'],
            '-1.0'             => [-1.0,                                  '-1.000000e+0'],
            'STDIN'            => [STDIN,                                 'resource#1'],
            'STDOUT'           => [STDOUT,                                'resource#2'],
            'a\nb'             => ["a\nb",                                '"a\nb"'],
            '[]'               => [[],                                    '#0[]'],
            '[1]'              => [[1],                                   '#0[1]'],
            '[1, 1]'           => [[1, 1],                                '#0[1, 1]'],
            '[1: 1]'           => [[1 => 1],                              '#0[1: 1]'],
            '[1: 1, 2: 2]'     => [[1 => 1, 2 => 2],                      '#0[1: 1, 2: 2]'],
            '[1, [1, 1]]'      => [[1, [1, 1]],                           '#0[1, #1[1, 1]]'],
            '[[1, 1], [1, 1]]' => [[[1, 1], [1, 1]],                      '#0[#1[1, 1], #2[1, 1]]'],
            '{a: 0}'           => [(object) ['a' => 0],                   '#0{a: 0}'],
            '{a: 0, b: 1}'     => [(object) ['a' => 0, 'b' => 1],         '#0{a: 0, b: 1}'],
            '{a: {a: 0}}'      => [(object) ['a' => (object) ['a' => 0]], '#0{a: #1{a: 0}}'],
            '{a: []}'          => [(object) ['a' => []],                  '#0{a: #0[]}'],
            'object'           => [new TestClass(),                       'TestClass#0{}'],
        ];
    }

    /**
     * @dataProvider exportData
     */
    public function testExport($value, $expected)
    {
        $copy = $value;

        $this->assertSame($expected, $this->subject->export($value));
        $this->assertSame($copy, $value);
    }

    public function testExportMaxDepthWithArrays()
    {
        $array = [];
        $value = [&$array, [&$array]];

        $this->assertSame('#0[~2]', $this->subject->export($value, 0));
        $this->assertSame('#0[#1[], #2[~1]]', $this->subject->export($value, 1));
        $this->assertSame('#0[#1[], #2[&1[]]]', $this->subject->export($value, 2));
        $this->assertSame('#0[#1[], #2[&1[]]]', $this->subject->export($value));
    }

    public function testExportMaxDepthWithObjects()
    {
        $object = (object) [];
        $value = (object) ['a' => &$object, 'b' => (object) ['a' => &$object]];

        $this->assertSame('#0{~2}', $this->subject->export($value, 0));
        $this->assertSame('#0{a: #1{}, b: #2{~1}}', $this->subject->export($value, 1));
        $this->assertSame('#0{a: #1{}, b: #2{a: &1{}}}', $this->subject->export($value, 2));
        $this->assertSame('#0{a: #1{}, b: #2{a: &1{}}}', $this->subject->export($value));
        $this->assertSame('#1{}', $this->subject->export($object, 0));
    }

    public function testExportRecursiveObject()
    {
        $value = new TestClass();
        $value->inner = $value;

        $this->assertSame('TestClass#0{inner: &0{}}', $this->subject->export($value));
    }

    public function testExportRecursiveArray()
    {
        $value = [];
        $value['inner'] = &$value;

        $this->assertSame('#0["inner": &0[]]', $this->subject->export($value));
    }

    public function testExportObjectPersistentIds()
    {
        $objectA = (object) [];
        $objectB = (object) [];

        $this->assertSame('#0{}', $this->subject->export($objectA));
        $this->assertSame('#1{}', $this->subject->export($objectB));
        $this->assertSame('#0{}', $this->subject->export($objectA));
    }

    public function testExportInaccessibleProperties()
    {
        $value = new TestClassE();

        $this->assertSame(TestClassE::class . '#0{privateProperty: "private"}', $this->subject->export($value));
    }

    public function testExportInaccessibleIneritedProperties()
    {
        $value = new TestDerivedClassA();
        $expected = TestDerivedClassA::class . '#0{' .
            'derivedPublic: "<derived-public>", ' .
            'derivedPrivate: "<derived-private>", ' .
            'basePrivate: "<derived-base-private>", ' .
            'derivedProtected: "<derived-protected>", ' .
            'basePublic: "<base-public>", ' .
            'baseProtected: "<base-protected>", ' .
            TestBaseClass::class . '.basePrivate: "<base-private>"}';

        $this->assertSame($expected, $this->subject->export($value));
    }

    public function testExportClosure()
    {
        $value = function () {};
        $line = __LINE__ - 1;

        $this->assertSame(
            'Closure#0{}[' . basename(__FILE__) . ':' . $line . ']',
            $this->subject->export($value)
        );
    }

    public function testExportExceptions()
    {
        $exceptionA = new RuntimeException();
        $exceptionB = new RuntimeException('message');
        $exceptionC = new RuntimeException('message', 111);
        $exceptionD = new RuntimeException('message', 111, $exceptionA);
        $exceptionE = new RuntimeException('message', 111, $exceptionA);
        $exceptionE->arbitrary = 'yolo';

        $this->assertSame('RuntimeException#0{}', $this->subject->export($exceptionA));
        $this->assertSame('RuntimeException#1{message: "message"}', $this->subject->export($exceptionB));
        $this->assertSame('RuntimeException#2{message: "message", code: 111}', $this->subject->export($exceptionC));
        $this->assertSame(
            'RuntimeException#3{message: "message", code: 111, previous: RuntimeException#0{}}',
            $this->subject->export($exceptionD)
        );
        $this->assertSame(
            'RuntimeException#4{message: "message", code: 111, previous: RuntimeException#0{}, arbitrary: "yolo"}',
            $this->subject->export($exceptionE)
        );
    }

    public function testExportGenerators()
    {
        $generator = call_user_func(
            function () {
                return;
                yield;
            }
        );

        $this->assertSame('Generator#0{}', $this->subject->export($generator));
    }

    public function testExportMocks()
    {
        $builder = $this->mockBuilderFactory->create(TestBaseClass::class)
            ->named('PhonyMockInlineExporterExportMocks');
        $mock = $builder->get();
        $handle = Phony::on($mock)->setLabel('label');
        $staticHandle = Phony::onStatic($mock);

        $this->assertSame(
            'PhonyMockInlineExporterExportMocks#0{basePublic: "<base-public>", basePrivate: "<base-private>", ' .
                'baseProtected: "<base-protected>"}[label]',
            $this->subject->export($mock)
        );
        $this->assertSame(
            'handle#1(PhonyMockInlineExporterExportMocks#0{basePublic: "<base-public>", basePrivate: ' .
                '"<base-private>", baseProtected: "<base-protected>"}[label])',
            $this->subject->export($handle)
        );
        $this->assertSame(
            'static-handle#2(PhonyMockInlineExporterExportMocks)',
            $this->subject->export($staticHandle)
        );
    }

    public function testExportSpies()
    {
        $spy = $this->spyFactory->create('implode')->setLabel('label');
        $closure = $this->spyFactory->create(function () {})->setLabel('label');
        $anonymous = $this->spyFactory->create()->setLabel('anonymous');
        $verifier = $this->spyVerifierFactory->createFromCallback('implode')->setLabel('verifier');
        $anonymousVerifier = $this->spyVerifierFactory->create()->setLabel('anonymous-verifier');
        $repeated = [$spy, $spy];

        $this->assertSame('spy#0(implode)[label]', $this->subject->export($spy));
        $this->assertSame(
            'spy#1(Closure#2{}[InlineExporterTest.php:' . (__LINE__ - 8) . '])[label]',
            $this->subject->export($closure)
        );
        $this->assertSame('spy#3[anonymous]', $this->subject->export($anonymous));
        $this->assertSame('spy#4(implode)[verifier]', $this->subject->export($verifier));
        $this->assertSame('spy#5[anonymous-verifier]', $this->subject->export($anonymousVerifier));
        $this->assertSame('#0[spy#0(implode)[label], &0()]', $this->subject->export($repeated));
    }

    public function testExportStubs()
    {
        $stub = $this->stubFactory->create('implode')->setLabel('label');
        $anonymous = $this->stubFactory->create()->setLabel('anonymous');
        $verifier = $this->stubVerifierFactory->createFromCallback('implode')->setLabel('verifier');
        $anonymousVerifier = $this->stubVerifierFactory->create()->setLabel('anonymous-verifier');
        $builderA = $this->mockBuilderFactory->create(TestClassA::class)
            ->named('PhonyMockInlineExporterExportSpiesA');
        $mockA = $builderA->get();
        $handleA = Phony::on($mockA)->setLabel('label');
        $handleA->testClassAMethodA->setLabel('method');
        $staticHandleA = Phony::onStatic($mockA);
        $staticHandleA->testClassAStaticMethodA->setLabel('static-method');
        $builderB = $this->mockBuilderFactory->create(TestInterfaceA::class)
            ->named('PhonyMockInlineExporterExportSpiesB');
        $mockB = $builderB->get();
        $handleB = Phony::on($mockB)->setLabel('label');
        $handleB->testClassAMethodA->setLabel('interface-method');
        $staticHandleB = Phony::onStatic($mockB);
        $staticHandleB->testClassAStaticMethodA->setLabel('interface-static-method');
        $builderC = $this->mockBuilderFactory->create()
            ->named('PhonyMockInlineExporterExportSpiesC')
            ->addMethod('method', function () {})
            ->addStaticMethod('staticMethod', function () {});
        $mockC = $builderC->get();
        $handleC = Phony::on($mockC)->setLabel('label');
        $handleC->method->setLabel('custom-method');
        $staticHandleC = Phony::onStatic($mockC);
        $staticHandleC->staticMethod->setLabel('custom-static-method');

        $this->assertSame('stub#0(implode)[label]', $this->subject->export($stub));
        $this->assertSame('stub#1[anonymous]', $this->subject->export($anonymous));
        $this->assertSame('stub#2(implode)[verifier]', $this->subject->export($verifier));
        $this->assertSame('stub#3[anonymous-verifier]', $this->subject->export($anonymousVerifier));
        $this->assertSame(
            'stub#4(TestClassA[label]->testClassAMethodA)[method]',
            $this->subject->export($handleA->testClassAMethodA)
        );
        $this->assertSame(
            'stub#5(TestClassA::testClassAStaticMethodA)[static-method]',
            $this->subject->export($staticHandleA->testClassAStaticMethodA)
        );
        $this->assertSame(
            'stub#6(TestInterfaceA[label]->testClassAMethodA)[interface-method]',
            $this->subject->export($handleB->testClassAMethodA)
        );
        $this->assertSame(
            'stub#7(TestInterfaceA::testClassAStaticMethodA)[interface-static-method]',
            $this->subject->export($staticHandleB->testClassAStaticMethodA)
        );
        $this->assertSame(
            'stub#8(PhonyMockInlineExporterExportSpiesC[label]->method)[custom-method]',
            $this->subject->export($handleC->method)
        );
        $this->assertSame(
            'stub#9(PhonyMockInlineExporterExportSpiesC::staticMethod)[custom-static-method]',
            $this->subject->export($staticHandleC->staticMethod)
        );
    }

    public function testExportGeneratorSpies()
    {
        $spy = $this->spyFactory->create(
            function () {
                return;
                yield;
            }
        );
        $generatorSpy = $spy();

        $this->assertSame('generator-spy#0(Generator#1{})', $this->subject->export($generatorSpy));
    }

    public function testExportIterableSpies()
    {
        $stub = $this->stubVerifierFactory->create()->setUseIterableSpies(true)->returns([]);
        $iterableSpy = $stub();

        $this->assertSame('iterable-spy#0(#0[])', $this->subject->export($iterableSpy));
    }

    /**
     * @requires PHP >= 7.4
     */
    public function testExportWeakReference()
    {
        $object = (object) ['a' => 'b'];
        $weakReference = WeakReference::create($object);
        $repeated = [$weakReference, $weakReference];

        $this->assertSame('weak#0(#1{a: "b"})', $this->subject->export($weakReference));
        $this->assertSame('#0[weak#0(#1{a: "b"}), &0()]', $this->subject->export($repeated));
    }

    public function testExportCallable()
    {
        $closure = function () {};
        $closureLine = __LINE__ - 1;
        $spy = $this->spyFactory->create('implode')->setLabel('spy');
        $anonymousSpy = $this->spyFactory->create()->setLabel('anonymous-spy');
        $spyVerifier = $this->spyVerifierFactory->createFromCallback('implode')->setLabel('spy-verifier');
        $anonymousSpyVerifier = $this->spyVerifierFactory->create()->setLabel('anonymous-spy-verifier');
        $stub = $this->stubFactory->create('implode')->setLabel('stub');
        $anonymousStub = $this->stubFactory->create()->setLabel('anonymous-stub');
        $stubVerifier = $this->stubVerifierFactory->createFromCallback('implode')->setLabel('stub-verifier');
        $anonymousStubVerifier = $this->stubVerifierFactory->create()->setLabel('anonymous-stub-verifier');
        $builderA = $this->mockBuilderFactory->create(TestClassA::class)
            ->named('PhonyMockInlineExporterExportCallableA')
            ->addMethod('method')
            ->addStaticMethod('staticMethod');
        $mockA = $builderA->get();
        $handleA = Phony::on($mockA)->setLabel('parent-class');
        $staticHandleA = Phony::onStatic($handleA);
        $builderB = $this->mockBuilderFactory->create(TestInterfaceA::class)
            ->named('PhonyMockInlineExporterExportCallableB');
        $mockB = $builderB->get();
        $handleB = Phony::on($mockB)->setLabel('interface');
        $staticHandleB = Phony::onStatic($handleB);

        $this->assertSame('implode', $this->subject->exportCallable('implode'));
        $this->assertSame(
            'Closure#0{}[InlineExporterTest.php:' . $closureLine . ']',
            $this->subject->exportCallable($closure)
        );
        $this->assertSame('implode[spy]', $this->subject->exportCallable($spy));
        $this->assertSame('spy#1[anonymous-spy]', $this->subject->exportCallable($anonymousSpy));
        $this->assertSame('implode[spy-verifier]', $this->subject->exportCallable($spyVerifier));
        $this->assertSame(
            'spy#2[anonymous-spy-verifier]',
            $this->subject->exportCallable($anonymousSpyVerifier)
        );
        $this->assertSame('implode[stub]', $this->subject->exportCallable($stub));
        $this->assertSame('stub#3[anonymous-stub]', $this->subject->exportCallable($anonymousStub));
        $this->assertSame('implode[stub-verifier]', $this->subject->exportCallable($stubVerifier));
        $this->assertSame(
            'stub#4[anonymous-stub-verifier]',
            $this->subject->exportCallable($anonymousStubVerifier)
        );
        $this->assertSame(
            'InlineExporterTest->testExportCallable',
            $this->subject->exportCallable([$this, __FUNCTION__])
        );
        $this->assertSame(
            'TestClassA->testClassAMethodA',
            $this->subject->exportCallable([$mockA, 'testClassAMethodA'])
        );
        $this->assertSame(
            'TestClassA[parent-class]->testClassAMethodA',
            $this->subject->exportCallable($handleA->testClassAMethodA)
        );
        $this->assertSame(
            'TestClassA::testClassAStaticMethodA',
            $this->subject->exportCallable(['PhonyMockInlineExporterExportCallableA', 'testClassAStaticMethodA'])
        );
        $this->assertSame(
            'TestClassA::testClassAStaticMethodA',
            $this->subject->exportCallable($staticHandleA->testClassAStaticMethodA)
        );
        $this->assertSame(
            'PhonyMockInlineExporterExportCallableA->method',
            $this->subject->exportCallable([$mockA, 'method'])
        );
        $this->assertSame(
            'PhonyMockInlineExporterExportCallableA::staticMethod',
            $this->subject->exportCallable(['PhonyMockInlineExporterExportCallableA', 'staticMethod'])
        );
        $this->assertSame(
            'PhonyMockInlineExporterExportCallableA::staticMethod',
            $this->subject->exportCallable($staticHandleA->staticMethod)
        );
        $this->assertSame(
            'TestInterfaceA->testClassAMethodA',
            $this->subject->exportCallable([$mockB, 'testClassAMethodA'])
        );
        $this->assertSame(
            'TestInterfaceA[interface]->testClassAMethodA',
            $this->subject->exportCallable($handleB->testClassAMethodA)
        );
        $this->assertSame(
            'TestInterfaceA::testClassAStaticMethodA',
            $this->subject->exportCallable(['PhonyMockInlineExporterExportCallableB', 'testClassAStaticMethodA'])
        );
        $this->assertSame(
            'TestInterfaceA::testClassAStaticMethodA',
            $this->subject->exportCallable($staticHandleB->testClassAStaticMethodA)
        );
    }

    public function testExportCallableWithTraits()
    {
        $builderA = $this->mockBuilderFactory->create(TestTraitA::class)
            ->named('PhonyMockInlineExporterExportCallableWithTraitsA');
        $mockA = $builderA->get();
        $handleA = Phony::on($mockA)->setLabel('trait');
        $staticHandleA = Phony::onStatic($handleA);

        $this->assertSame(
            'PhonyMockInlineExporterExportCallableWithTraitsA->testClassAMethodB',
            $this->subject->exportCallable([$mockA, 'testClassAMethodB'])
        );
        $this->assertSame(
            'PhonyMockInlineExporterExportCallableWithTraitsA[trait]->testClassAMethodB',
            $this->subject->exportCallable($handleA->testClassAMethodB)
        );
        $this->assertSame(
            'PhonyMockInlineExporterExportCallableWithTraitsA::testClassAStaticMethodA',
            $this->subject->exportCallable(
                ['PhonyMockInlineExporterExportCallableWithTraitsA', 'testClassAStaticMethodA']
            )
        );
        $this->assertSame(
            'PhonyMockInlineExporterExportCallableWithTraitsA::testClassAStaticMethodA',
            $this->subject->exportCallable($staticHandleA->testClassAStaticMethodA)
        );
    }

    public function testReset()
    {
        $objectA = (object) [];
        $objectB = (object) [];
        $this->subject->export($objectA);
        $this->subject->export($objectB);
        $this->subject->reset();

        $this->assertSame('#0{}', $this->subject->export($objectB));
        $this->assertSame('#1{}', $this->subject->export($objectA));
    }

    public function testInstance()
    {
        $class = get_class($this->subject);
        $reflector = new ReflectionClass($class);
        $property = $reflector->getProperty('instance');
        $property->setAccessible(true);
        $property->setValue(null, null);
        $instance = $class::instance();

        $this->assertInstanceOf($class, $instance);
        $this->assertSame($instance, $class::instance());
    }
}
