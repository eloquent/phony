<?php

declare(strict_types=1);

namespace Eloquent\Phony\Spy;

use AllowDynamicProperties;
use Eloquent\Phony\Test\Facade\FacadeContainer;
use Eloquent\Phony\Test\SpyVerifierFactory as TestNamespace;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[AllowDynamicProperties]
class SpyVerifierFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        $this->container = new FacadeContainer();
        $this->subject = $this->container->spyVerifierFactory;
    }

    public function testCreate()
    {
        $spy = $this->container->spyFactory->create(null)->setLabel('0');
        $expected = new SpyVerifier(
            $spy,
            $this->container->matcherFactory,
            $this->container->matcherVerifier,
            $this->container->generatorVerifierFactory,
            $this->container->iterableVerifierFactory,
            $this->container->callVerifierFactory,
            $this->container->assertionRecorder,
            $this->container->assertionRenderer
        );
        $actual = $this->subject->create($spy);

        $this->assertEquals($expected, $actual);
        $this->assertSame($spy, $actual->spy());
    }

    public function testCreateFromCallback()
    {
        $callback = function () {};
        $spy = $this->container->spyFactory->create($callback)->setLabel('1');
        $expected = new SpyVerifier(
            $spy,
            $this->container->matcherFactory,
            $this->container->matcherVerifier,
            $this->container->generatorVerifierFactory,
            $this->container->iterableVerifierFactory,
            $this->container->callVerifierFactory,
            $this->container->assertionRecorder,
            $this->container->assertionRenderer
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
