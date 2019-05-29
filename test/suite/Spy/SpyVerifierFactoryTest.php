<?php

declare(strict_types=1);

namespace Eloquent\Phony\Spy;

use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Assertion\ExceptionAssertionRecorder;
use Eloquent\Phony\Call\CallFactory;
use Eloquent\Phony\Call\CallVerifierFactory;
use Eloquent\Phony\Hook\FunctionHookManager;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Matcher\MatcherFactory;
use Eloquent\Phony\Matcher\MatcherVerifier;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Test\SpyVerifierFactory as TestNamespace;
use Eloquent\Phony\Verification\GeneratorVerifierFactory;
use Eloquent\Phony\Verification\IterableVerifierFactory;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class SpyVerifierFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        $this->spyFactory = new SpyFactory(
            new Sequencer(),
            CallFactory::instance(),
            Invoker::instance(),
            GeneratorSpyFactory::instance(),
            IterableSpyFactory::instance()
        );
        $this->matcherFactory = MatcherFactory::instance();
        $this->matcherVerifier = new MatcherVerifier();
        $this->generatorVerifierFactory = GeneratorVerifierFactory::instance();
        $this->iterableVerifierFactory = IterableVerifierFactory::instance();
        $this->callVerifierFactory = CallVerifierFactory::instance();
        $this->assertionRecorder = ExceptionAssertionRecorder::instance();
        $this->assertionRecorder->setCallVerifierFactory($this->callVerifierFactory);
        $this->assertionRenderer = AssertionRenderer::instance();
        $this->functionHookManager = FunctionHookManager::instance();
        $this->subject = new SpyVerifierFactory(
            $this->spyFactory,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->iterableVerifierFactory,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->functionHookManager
        );
    }

    public function testCreate()
    {
        $spy = $this->spyFactory->create()->setLabel('0');
        $expected = new SpyVerifier(
            $spy,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->iterableVerifierFactory,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer
        );
        $actual = $this->subject->create($spy);

        $this->assertEquals($expected, $actual);
        $this->assertSame($spy, $actual->spy());
    }

    public function testCreateDefaults()
    {
        $spy = $this->spyFactory->create()->setLabel('1');
        $expected = new SpyVerifier(
            $spy,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->iterableVerifierFactory,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer
        );
        $actual = $this->subject->create();

        $this->assertEquals($expected, $actual);
    }

    public function testCreateFromCallback()
    {
        $callback = function () {};
        $spy = $this->spyFactory->create($callback)->setLabel('1');
        $expected = new SpyVerifier(
            $spy,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->iterableVerifierFactory,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer
        );
        $actual = $this->subject->createFromCallback($callback);

        $this->assertEquals($expected, $actual);
        $this->assertEquals($spy, $actual->spy());
    }

    public function testCreateGlobal()
    {
        $actual = $this->subject->createGlobal('sprintf', TestNamespace::class);

        $this->assertSame('a, b, c', TestNamespace\sprintf('%s, %s, %s', 'a', 'b', 'c'));
        $this->assertTrue((bool) $actual->checkCalledWith('%s, %s, %s', 'a', 'b', 'c'));
    }

    public function testCreateGlobalWithReferenceParameters()
    {
        $this->subject->createGlobal('preg_match', TestNamespace::class);

        TestNamespace\preg_match('/./', 'a', $matches);

        $this->assertSame([0 => 'a'], $matches);
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
