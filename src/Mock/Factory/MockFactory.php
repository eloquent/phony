<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Factory;

use Eloquent\Phony\Call\Argument\Arguments;
use Eloquent\Phony\Call\Argument\ArgumentsInterface;
use Eloquent\Phony\Mock\Builder\MockBuilderInterface;
use Eloquent\Phony\Mock\Exception\ClassExistsException;
use Eloquent\Phony\Mock\Exception\MockExceptionInterface;
use Eloquent\Phony\Mock\Exception\MockGenerationFailedException;
use Eloquent\Phony\Mock\Generator\MockGenerator;
use Eloquent\Phony\Mock\Generator\MockGeneratorInterface;
use Eloquent\Phony\Mock\MockInterface;
use Eloquent\Phony\Mock\Proxy\Factory\ProxyFactory;
use Eloquent\Phony\Mock\Proxy\Factory\ProxyFactoryInterface;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Sequencer\SequencerInterface;
use ReflectionClass;

/**
 * Creates mock instances.
 *
 * @internal
 */
class MockFactory implements MockFactoryInterface
{
    /**
     * Get the static instance of this factory.
     *
     * @return MockFactoryInterface The static factory.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Cosntruct a new mock factory.
     *
     * @param SequencerInterface|null     $idSequencer  The identifier sequencer to use.
     * @param MockGeneratorInterface|null $generator    The generator to use.
     * @param ProxyFactoryInterface|null  $proxyFactory The proxy factory to use.
     */
    public function __construct(
        SequencerInterface $idSequencer = null,
        MockGeneratorInterface $generator = null,
        ProxyFactoryInterface $proxyFactory = null
    ) {
        if (null === $idSequencer) {
            $idSequencer = Sequencer::sequence('mock-id');
        }
        if (null === $generator) {
            $generator = MockGenerator::instance();
        }
        if (null === $proxyFactory) {
            $proxyFactory = ProxyFactory::instance();
        }

        $this->idSequencer = $idSequencer;
        $this->generator = $generator;
        $this->proxyFactory = $proxyFactory;
        $this->definitions = array();
    }

    /**
     * Get the identifier sequencer.
     *
     * @return SequencerInterface The identifier sequencer.
     */
    public function idSequencer()
    {
        return $this->idSequencer;
    }

    /**
     * Get the generator.
     *
     * @return MockGeneratorInterface The generator.
     */
    public function generator()
    {
        return $this->generator;
    }

    /**
     * Get the proxy factory.
     *
     * @return ProxyFactoryInterface The proxy factory.
     */
    public function proxyFactory()
    {
        return $this->proxyFactory;
    }

    /**
     * Create the mock class for the supplied builder.
     *
     * @param MockBuilderInterface $builder   The builder.
     * @param boolean|null         $createNew True if a new class should be created even when a compatible one exists.
     *
     * @return ReflectionClass        The class.
     * @throws MockExceptionInterface If the mock generation fails.
     */
    public function createMockClass(
        MockBuilderInterface $builder,
        $createNew = null
    ) {
        if (null === $createNew) {
            $createNew = false;
        }

        $definition = $builder->definition();

        if (!$createNew) {
            foreach ($this->definitions as $tuple) {
                if ($definition->isEqualTo($tuple[0])) {
                    return $tuple[1];
                }
            }
        }

        $className = $this->generator->generateClassName($definition);

        if (class_exists($className, false)) {
            throw new ClassExistsException($className);
        }

        $source = $this->generator->generate($definition, $className);
        @eval($source);

        if (!class_exists($className, false)) {
            throw new MockGenerationFailedException(
                $definition,
                $source,
                error_get_last()
            );
        }

        $class = new ReflectionClass($className);

        $customMethodsProperty = $class->getProperty('_customMethods');
        $customMethodsProperty->setAccessible(true);
        $customMethodsProperty->setValue(
            null,
            array_merge(
                $definition->customStaticMethods(),
                $definition->customMethods()
            )
        );

        $proxyProperty = $class->getProperty('_staticProxy');
        $proxyProperty->setAccessible(true);
        $proxyProperty
            ->setValue(null, $this->proxyFactory->createStubbingStatic($class));

        $this->definitions[] = array($definition, $class);

        return $class;
    }

    /**
     * Create a new mock instance for the supplied builder.
     *
     * @param MockBuilderInterface                         $builder   The builder.
     * @param ArgumentsInterface|array<integer,mixed>|null $arguments The constructor arguments, or null to bypass the constructor.
     * @param string|null                                  $id        The identifier.
     *
     * @return MockInterface          The newly created mock.
     * @throws MockExceptionInterface If the mock generation fails.
     */
    public function createMock(
        MockBuilderInterface $builder,
        $arguments = null,
        $id = null
    ) {
        if (null === $id) {
            $id = strval($this->idSequencer->next());
        }

        $class = $builder->build();
        $mock = $class->newInstanceArgs();
        $proxy = $this->proxyFactory->createStubbing($mock, $id);

        $proxyProperty = $class->getProperty('_proxy');
        $proxyProperty->setAccessible(true);
        $proxyProperty->setValue($mock, $proxy);

        if (null !== $arguments) {
            $proxy->constructWith($arguments);
        }

        return $mock;
    }

    private static $instance;
    private $idSequencer;
    private $generator;
    private $proxyFactory;
    private $definitions;
}
