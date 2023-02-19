<?php

declare(strict_types=1);

namespace Eloquent\Phony\Stub;

use AllowDynamicProperties;
use Eloquent\Phony\Test\Facade\FacadeContainer;
use Eloquent\Phony\Test\StubVerifierFactory as TestNamespace;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

#[AllowDynamicProperties]
class StubVerifierFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        $this->container = FacadeContainer::withTestCallFactory();
        $this->callFactory = $this->container->callFactory;
        $this->eventFactory = $this->container->eventFactory;

        $this->subject = $this->container->stubVerifierFactory;
    }

    public function testCreate()
    {
        $stub = $this->container->stubFactory->create(null, null);
        $spy = $this->container->spyFactory->create($stub)->setLabel('1');
        $expected = new StubVerifier(
            $stub,
            $spy,
            $this->container->matcherFactory,
            $this->container->matcherVerifier,
            $this->container->generatorVerifierFactory,
            $this->container->iterableVerifierFactory,
            $this->container->callVerifierFactory,
            $this->container->assertionRecorder,
            $this->container->assertionRenderer,
            $this->container->generatorAnswerBuilderFactory
        );
        $expected->setSelf($expected);
        $actual = $this->subject->create($stub);

        $this->assertEquals($expected, $actual);
        $this->assertSame($stub, $actual->stub());
    }

    public function testCreateFromCallback()
    {
        $callback = function () {};
        $stub = $this->container->stubFactory->create($callback, null)->setLabel('1');
        $spy = $this->container->spyFactory->create($stub)->setLabel('1');
        $expected = new StubVerifier(
            $stub,
            $spy,
            $this->container->matcherFactory,
            $this->container->matcherVerifier,
            $this->container->generatorVerifierFactory,
            $this->container->iterableVerifierFactory,
            $this->container->callVerifierFactory,
            $this->container->assertionRecorder,
            $this->container->assertionRenderer,
            $this->container->generatorAnswerBuilderFactory
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
}
