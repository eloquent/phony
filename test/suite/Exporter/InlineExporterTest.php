<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2017 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

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
use Eloquent\Phony\Test\Properties\TestDerivedClassA;
use Eloquent\Phony\Test\TestClassE;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use RuntimeException;
use TestClass;

class InlineExporterTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
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
        return array(
            'null'             => array(null,                                            'null'),
            'true'             => array(true,                                            'true'),
            'false'            => array(false,                                           'false'),
            '0'                => array(0,                                               '0'),
            '-0'               => array(-0,                                              '0'),
            '1'                => array(1,                                               '1'),
            '-1'               => array(-1,                                              '-1'),
            '0.0'              => array(0.0,                                             '0.000000e+0'),
            '-0.0'             => array(-0.0,                                            '0.000000e+0'),
            '1.0'              => array(1.0,                                             '1.000000e+0'),
            '-1.0'             => array(-1.0,                                            '-1.000000e+0'),
            'STDIN'            => array(STDIN,                                           'resource#1'),
            'STDOUT'           => array(STDOUT,                                          'resource#2'),
            'a\nb'             => array("a\nb",                                          '"a\nb"'),
            '[]'               => array(array(),                                         '#0[]'),
            '[1]'              => array(array(1),                                        '#0[1]'),
            '[1, 1]'           => array(array(1, 1),                                     '#0[1, 1]'),
            '[1: 1]'           => array(array(1 => 1),                                   '#0[1: 1]'),
            '[1: 1, 2: 2]'     => array(array(1 => 1, 2 => 2),                           '#0[1: 1, 2: 2]'),
            '[1, [1, 1]]'      => array(array(1, array(1, 1)),                           '#0[1, #1[1, 1]]'),
            '[[1, 1], [1, 1]]' => array(array(array(1, 1), array(1, 1)),                 '#0[#1[1, 1], #2[1, 1]]'),
            '{a: 0}'           => array((object) array('a' => 0),                        '#0{a: 0}'),
            '{a: 0, b: 1}'     => array((object) array('a' => 0, 'b' => 1),              '#0{a: 0, b: 1}'),
            '{a: {a: 0}}'      => array((object) array('a' => (object) array('a' => 0)), '#0{a: #1{a: 0}}'),
            '{a: []}'          => array((object) array('a' => array()),                  '#0{a: #0[]}'),
            'object'           => array(new TestClass(),                                 'TestClass#0{}'),
        );
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
        $array = array();
        $value = array(&$array, array(&$array));

        $this->assertSame('#0[~2]', $this->subject->export($value, 0));
        $this->assertSame('#0[#1[], #2[~1]]', $this->subject->export($value, 1));
        $this->assertSame('#0[#1[], #2[&1[]]]', $this->subject->export($value, 2));
        $this->assertSame('#0[#1[], #2[&1[]]]', $this->subject->export($value));
    }

    public function testExportMaxDepthWithObjects()
    {
        $object = (object) array();
        $value = (object) array('a' => &$object, 'b' => (object) array('a' => &$object));

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
        $value = array();
        $value['inner'] = &$value;

        $this->assertSame('#0["inner": &0[]]', $this->subject->export($value));
    }

    public function testExportObjectPersistentIds()
    {
        $objectA = (object) array();
        $objectB = (object) array();

        $this->assertSame('#0{}', $this->subject->export($objectA));
        $this->assertSame('#1{}', $this->subject->export($objectB));
        $this->assertSame('#0{}', $this->subject->export($objectA));
    }

    public function testExportInaccessibleProperties()
    {
        $value = new TestClassE();

        $this->assertSame(
            'Eloquent\Phony\Test\TestClassE#0{privateProperty: "private"}',
            $this->subject->export($value)
        );
    }

    public function testExportInaccessibleIneritedProperties()
    {
        $value = new TestDerivedClassA();

        if (
            !$this->featureDetector->isSupported('runtime.hhvm') &&
            version_compare(PHP_VERSION, '5.4.x', '>=')
        ) {
            $expected = 'Eloquent\Phony\Test\Properties\TestDerivedClassA#0{' .
                'derivedPublic: "<derived-public>", ' .
                'derivedPrivate: "<derived-private>", ' .
                'basePrivate: "<derived-base-private>", ' .
                'derivedProtected: "<derived-protected>", ' .
                'basePublic: "<base-public>", ' .
                'baseProtected: "<base-protected>", ' .
                'Eloquent\Phony\Test\Properties\TestBaseClass.basePrivate: "<base-private>"}';
        } else {
            $expected = 'Eloquent\Phony\Test\Properties\TestDerivedClassA#0{' .
                'derivedPublic: "<derived-public>", ' .
                'derivedPrivate: "<derived-private>", ' .
                'basePrivate: "<derived-base-private>", ' .
                'derivedProtected: "<derived-protected>", ' .
                'basePublic: "<base-public>", ' .
                'Eloquent\Phony\Test\Properties\TestBaseClass.basePrivate: "<base-private>", ' .
                'baseProtected: "<base-protected>"}';
        }

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
        if (!$this->featureDetector->isSupported('generator')) {
            $this->markTestSkipped('Requires generators.');
        }

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
        $builder = $this->mockBuilderFactory->create('Eloquent\Phony\Test\Properties\TestBaseClass')
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
        $anonymousVerifier = $this->spyVerifierFactory->createFromCallback()->setLabel('anonymous-verifier');
        $repeated = array($spy, $spy);

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
        $anonymousVerifier = $this->stubVerifierFactory->createFromCallback()->setLabel('anonymous-verifier');
        $builderA = $this->mockBuilderFactory->create('Eloquent\Phony\Test\TestClassA')
            ->named('PhonyMockInlineExporterExportSpiesA');
        $mockA = $builderA->get();
        $handleA = Phony::on($mockA)->setLabel('label');
        $handleA->testClassAMethodA->setLabel('method');
        $staticHandleA = Phony::onStatic($mockA);
        $staticHandleA->testClassAStaticMethodA->setLabel('static-method');
        $builderB = $this->mockBuilderFactory->create('Eloquent\Phony\Test\TestInterfaceA')
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
        if (!$this->featureDetector->isSupported('generator')) {
            $this->markTestSkipped('Requires generators.');
        }

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
        $stub = $this->stubVerifierFactory->create()->setUseIterableSpies(true)->returns(array());
        $iterableSpy = $stub();

        $this->assertSame('iterable-spy#0(#0[])', $this->subject->export($iterableSpy));
    }

    public function testExportCallable()
    {
        $closure = function () {};
        $closureLine = __LINE__ - 1;
        $spy = $this->spyFactory->create('implode')->setLabel('spy');
        $anonymousSpy = $this->spyFactory->create()->setLabel('anonymous-spy');
        $spyVerifier = $this->spyVerifierFactory->createFromCallback('implode')->setLabel('spy-verifier');
        $anonymousSpyVerifier = $this->spyVerifierFactory->createFromCallback()->setLabel('anonymous-spy-verifier');
        $stub = $this->stubFactory->create('implode')->setLabel('stub');
        $anonymousStub = $this->stubFactory->create()->setLabel('anonymous-stub');
        $stubVerifier = $this->stubVerifierFactory->createFromCallback('implode')->setLabel('stub-verifier');
        $anonymousStubVerifier = $this->stubVerifierFactory->createFromCallback()->setLabel('anonymous-stub-verifier');
        $builderA = $this->mockBuilderFactory->create('Eloquent\Phony\Test\TestClassA')
            ->named('PhonyMockInlineExporterExportCallableA')
            ->addMethod('method')
            ->addStaticMethod('staticMethod');
        $mockA = $builderA->get();
        $handleA = Phony::on($mockA)->setLabel('parent-class');
        $staticHandleA = Phony::onStatic($handleA);
        $builderB = $this->mockBuilderFactory->create('Eloquent\Phony\Test\TestInterfaceA')
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
        $this->assertSame('InlineExporterTest->testExportCallable', $this->subject->exportCallable(__METHOD__));
        $this->assertSame(
            'TestClassA->testClassAMethodA',
            $this->subject->exportCallable(array($mockA, 'testClassAMethodA'))
        );
        $this->assertSame(
            'TestClassA[parent-class]->testClassAMethodA',
            $this->subject->exportCallable($handleA->testClassAMethodA)
        );
        $this->assertSame(
            'TestClassA::testClassAStaticMethodA',
            $this->subject->exportCallable(array('PhonyMockInlineExporterExportCallableA', 'testClassAStaticMethodA'))
        );
        $this->assertSame(
            'TestClassA::testClassAStaticMethodA',
            $this->subject->exportCallable($staticHandleA->testClassAStaticMethodA)
        );
        $this->assertSame(
            'PhonyMockInlineExporterExportCallableA->method',
            $this->subject->exportCallable(array($mockA, 'method'))
        );
        $this->assertSame(
            'PhonyMockInlineExporterExportCallableA::staticMethod',
            $this->subject->exportCallable(array('PhonyMockInlineExporterExportCallableA', 'staticMethod'))
        );
        $this->assertSame(
            'PhonyMockInlineExporterExportCallableA::staticMethod',
            $this->subject->exportCallable($staticHandleA->staticMethod)
        );
        $this->assertSame(
            'TestInterfaceA->testClassAMethodA',
            $this->subject->exportCallable(array($mockB, 'testClassAMethodA'))
        );
        $this->assertSame(
            'TestInterfaceA[interface]->testClassAMethodA',
            $this->subject->exportCallable($handleB->testClassAMethodA)
        );
        $this->assertSame(
            'TestInterfaceA::testClassAStaticMethodA',
            $this->subject->exportCallable(array('PhonyMockInlineExporterExportCallableB', 'testClassAStaticMethodA'))
        );
        $this->assertSame(
            'TestInterfaceA::testClassAStaticMethodA',
            $this->subject->exportCallable($staticHandleB->testClassAStaticMethodA)
        );
    }

    public function testExportCallableWithTraits()
    {
        if (!$this->featureDetector->isSupported('trait')) {
            $this->markTestSkipped('Requires traits.');
        }

        $builderA = $this->mockBuilderFactory->create('Eloquent\Phony\Test\TestTraitA')
            ->named('PhonyMockInlineExporterExportCallableWithTraitsA');
        $mockA = $builderA->get();
        $handleA = Phony::on($mockA)->setLabel('trait');
        $staticHandleA = Phony::onStatic($handleA);

        $this->assertSame(
            'PhonyMockInlineExporterExportCallableWithTraitsA->testClassAMethodB',
            $this->subject->exportCallable(array($mockA, 'testClassAMethodB'))
        );
        $this->assertSame(
            'PhonyMockInlineExporterExportCallableWithTraitsA[trait]->testClassAMethodB',
            $this->subject->exportCallable($handleA->testClassAMethodB)
        );
        $this->assertSame(
            'PhonyMockInlineExporterExportCallableWithTraitsA::testClassAStaticMethodA',
            $this->subject->exportCallable(
                array('PhonyMockInlineExporterExportCallableWithTraitsA', 'testClassAStaticMethodA')
            )
        );
        $this->assertSame(
            'PhonyMockInlineExporterExportCallableWithTraitsA::testClassAStaticMethodA',
            $this->subject->exportCallable($staticHandleA->testClassAStaticMethodA)
        );
    }

    public function testReset()
    {
        $objectA = (object) array();
        $objectB = (object) array();
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
