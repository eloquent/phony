<?php

declare(strict_types=1);

namespace Eloquent\Phony\Stub;

use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Assertion\ExceptionAssertionRecorder;
use Eloquent\Phony\Call\CallVerifierFactory;
use Eloquent\Phony\Exporter\InlineExporter;
use Eloquent\Phony\Hook\FunctionHookManager;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Matcher\MatcherFactory;
use Eloquent\Phony\Matcher\MatcherVerifier;
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Spy\GeneratorSpyFactory;
use Eloquent\Phony\Spy\IterableSpyFactory;
use Eloquent\Phony\Spy\SpyFactory;
use Eloquent\Phony\Stub\Answer\Builder\GeneratorAnswerBuilderFactory;
use Eloquent\Phony\Test\StubVerifierFactory as TestNamespace;
use Eloquent\Phony\Test\TestCallFactory;
use Eloquent\Phony\Test\WithDynamicProperties;
use Eloquent\Phony\Verification\GeneratorVerifierFactory;
use Eloquent\Phony\Verification\IterableVerifierFactory;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class StubVerifierFactoryTest extends TestCase
{
    use WithDynamicProperties;

    protected function setUp(): void
    {
        $this->callFactory = new TestCallFactory();
        $this->spyFactory = new SpyFactory(
            new Sequencer(),
            $this->callFactory,
            Invoker::instance(),
            GeneratorSpyFactory::instance(),
            IterableSpyFactory::instance()
        );
        $this->matcherFactory = MatcherFactory::instance();
        $this->matcherVerifier = new MatcherVerifier();
        $this->stubFactory = new StubFactory(
            new Sequencer(),
            $this->matcherFactory,
            $this->matcherVerifier,
            Invoker::instance(),
            InvocableInspector::instance(),
            new EmptyValueFactory(FeatureDetector::instance()),
            GeneratorAnswerBuilderFactory::instance(),
            InlineExporter::instance()
        );
        $this->generatorVerifierFactory = GeneratorVerifierFactory::instance();
        $this->iterableVerifierFactory = IterableVerifierFactory::instance();
        $this->callVerifierFactory = CallVerifierFactory::instance();
        $this->assertionRecorder = ExceptionAssertionRecorder::instance();
        $this->assertionRenderer = AssertionRenderer::instance();
        $this->generatorAnswerBuilderFactory = GeneratorAnswerBuilderFactory::instance();
        $this->functionHookManager = FunctionHookManager::instance();
        $this->subject = new StubVerifierFactory(
            $this->stubFactory,
            $this->spyFactory,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->iterableVerifierFactory,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->generatorAnswerBuilderFactory,
            $this->functionHookManager
        );
    }

    public function testCreate()
    {
        $stub = $this->stubFactory->create(null, null);
        $spy = $this->spyFactory->create($stub)->setLabel('1');
        $expected = new StubVerifier(
            $stub,
            $spy,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->iterableVerifierFactory,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->generatorAnswerBuilderFactory
        );
        $expected->setSelf($expected);
        $actual = $this->subject->create($stub);

        $this->assertEquals($expected, $actual);
        $this->assertSame($stub, $actual->stub());
    }

    public function testCreateFromCallback()
    {
        $callback = function () {};
        $stub = $this->stubFactory->create($callback, null)->setLabel('1');
        $spy = $this->spyFactory->create($stub)->setLabel('1');
        $expected = new StubVerifier(
            $stub,
            $spy,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->iterableVerifierFactory,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->generatorAnswerBuilderFactory
        );
        $expected->setSelf($expected);
        $actual = $this->subject->createFromCallback($callback);

        $this->assertEquals($expected, $actual);
        $this->assertTrue($actual->useGeneratorSpies());
        $this->assertFalse($actual->useIterableSpies());
    }

    public function testCreateGlobal()
    {
        $actual = $this->subject->createGlobal('sprintf', TestNamespace::class);
        $actual->with('a', 'b')->returns('c');
        $actual->with('%s, %s, %s', 'a', 'b', 'c')->forwards();

        $this->assertSame('c', TestNamespace\sprintf('a', 'b'));
        $this->assertSame('a, b, c', TestNamespace\sprintf('%s, %s, %s', 'a', 'b', 'c'));
        $this->assertEmpty(TestNamespace\sprintf('x', 'y'));
    }

    public function testCreateGlobalWithReferenceParameters()
    {
        $actual = $this->subject->createGlobal('preg_match', TestNamespace::class);
        $actual->setsArgument(2, ['a', 'b']);

        TestNamespace\preg_match('/./', 'a', $matches);

        $this->assertSame(['a', 'b'], $matches);
    }

    public function testCreateGlobalFailureWithNonGlobal()
    {
        $this->expectException(
            InvalidArgumentException::class,
            'Only functions in the global namespace are supported.'
        );
        $this->subject->createGlobal('Namespaced\\function', TestNamespace::class);
    }

    public function testCreateGlobalFailureEmptyNamespace()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The supplied namespace must not be empty.');
        $this->subject->createGlobal('implode', '');
    }

    public function testCreateGlobalFailureGlobalNamespace()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The supplied namespace must not be empty.');
        $this->subject->createGlobal('implode', '\\');
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
