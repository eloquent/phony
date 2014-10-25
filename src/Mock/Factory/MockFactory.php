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
use Eloquent\Phony\Hash\HashGenerator;
use Eloquent\Phony\Hash\HashGeneratorInterface;
use Eloquent\Phony\Mock\Builder\Definition\Method\MethodDefinitionInterface;
use Eloquent\Phony\Mock\Builder\MockBuilderInterface;
use Eloquent\Phony\Mock\Exception\ClassExistsException;
use Eloquent\Phony\Mock\Exception\MockExceptionInterface;
use Eloquent\Phony\Mock\Exception\MockGenerationFailedException;
use Eloquent\Phony\Mock\Generator\MockGenerator;
use Eloquent\Phony\Mock\Generator\MockGeneratorInterface;
use Eloquent\Phony\Mock\Method\WrappedMethod;
use Eloquent\Phony\Mock\MockInterface;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Sequencer\SequencerInterface;
use Eloquent\Phony\Spy\Factory\SpyFactory;
use Eloquent\Phony\Spy\Factory\SpyFactoryInterface;
use Eloquent\Phony\Spy\SpyInterface;
use Eloquent\Phony\Stub\Factory\StubFactory;
use Eloquent\Phony\Stub\Factory\StubFactoryInterface;
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
     * @param SequencerInterface|null     $idSequencer   The identifier sequencer to use.
     * @param MockGeneratorInterface|null $generator     The generator to use.
     * @param HashGeneratorInterface|null $hashGenerator The hash generator to use.
     * @param StubFactoryInterface|null   $stubFactory   The stub factory to use.
     * @param SpyFactoryInterface|null    $spyFactory    The spy factory to use.
     */
    public function __construct(
        SequencerInterface $idSequencer = null,
        MockGeneratorInterface $generator = null,
        HashGeneratorInterface $hashGenerator = null,
        StubFactoryInterface $stubFactory = null,
        SpyFactoryInterface $spyFactory = null
    ) {
        if (null === $idSequencer) {
            $idSequencer = Sequencer::sequence('mock-id');
        }
        if (null === $generator) {
            $generator = MockGenerator::instance();
        }
        if (null === $hashGenerator) {
            $hashGenerator = HashGenerator::instance();
        }
        if (null === $stubFactory) {
            $stubFactory = StubFactory::instance();
        }
        if (null === $spyFactory) {
            $spyFactory = SpyFactory::instance();
        }

        $this->idSequencer = $idSequencer;
        $this->generator = $generator;
        $this->hashGenerator = $hashGenerator;
        $this->stubFactory = $stubFactory;
        $this->spyFactory = $spyFactory;
        $this->classes = array();
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
     * Get the hash generator.
     *
     * @return HashGeneratorInterface The hash generator.
     */
    public function hashGenerator()
    {
        return $this->hashGenerator;
    }

    /**
     * Get the stub factory.
     *
     * @return StubFactoryInterface The stub factory.
     */
    public function stubFactory()
    {
        return $this->stubFactory;
    }

    /**
     * Get the spy factory.
     *
     * @return SpyFactoryInterface The spy factory.
     */
    public function spyFactory()
    {
        return $this->spyFactory;
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
        $hash = $this->hashGenerator->hash($definition->toMap());

        if (!$createNew && isset($this->classes[$hash])) {
            return $this->classes[$hash];
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

        $property = $class->getProperty('_staticStubs');
        $property->setAccessible(true);
        $property->setValue(
            null,
            $this->createStubs(
                $class,
                $definition->methods()->staticMethods()
            )
        );

        return $this->classes[$hash] = $class;
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

        $stubsProperty = $class->getProperty('_stubs');
        $stubsProperty->setAccessible(true);
        $stubsProperty->setValue(
            $mock,
            $this->createStubs(
                $class,
                $builder->definition()->methods()->methods(),
                $mock
            )
        );

        $idProperty = $class->getProperty('_mockId');
        $idProperty->setAccessible(true);
        $idProperty->setValue($mock, $id);

        if (null !== $arguments) {
            if ($parentClass = $class->getParentClass()) {
                if ($constructor = $parentClass->getConstructor()) {
                    $method = $class->getMethod('_callParent');
                    $method->setAccessible(true);
                    $method->invoke(
                        $mock,
                        $constructor->getName(),
                        Arguments::adapt($arguments)
                    );
                }
            }
        }

        return $mock;
    }

    /**
     * Create the stubs for a list of methods.
     *
     * @param ReflectionClass    $class The mock class.
     * @param array<string,MethodDefinitionInterface> The methods.
     * @param MockInterface|null $mock  The mock, or null for static stubs.
     *
     * @return array<string,SpyInterface> The stubs.
     */
    public function createStubs(
        ReflectionClass $class,
        array $methods,
        MockInterface $mock = null
    ) {
        $stubs = array();

        $callParentStatic = $class->getMethod('_callParentStatic');
        $callParentStatic->setAccessible(true);

        $callParentInstance = $class->getMethod('_callParent');
        $callParentInstance->setAccessible(true);

        foreach ($methods as $method) {
            if ($method->isCustom()) {
                $stub = $this->stubFactory->create($method->callback(), $mock);
            } else {
                if ($method->isStatic()) {
                    $callParentMethod = $callParentStatic;
                } else {
                    $callParentMethod = $callParentInstance;
                }

                $stub = $this->stubFactory->create(
                    new WrappedMethod(
                        $callParentMethod,
                        $method->method(),
                        $mock
                    ),
                    $mock
                );
            }

            $stubs[$method->name()] = $this->spyFactory->create($stub);
        }

        return $stubs;
    }

    /**
     * Create a magic stub.
     *
     * @param ReflectionClass    $class The mock class.
     * @param string             $name  The method name.
     * @param MockInterface|null $mock  The mock, or null for a static stub.
     *
     * @return SpyInterface The stub.
     */
    public function createMagicStub(
        ReflectionClass $class,
        $name,
        MockInterface $mock = null
    ) {
        if ($mock) {
            $callMethod = $class->getMethod('_callMagic');
        } else {
            $callMethod = $class->getMethod('_callMagicStatic');
        }

        $callMethod->setAccessible(true);

        $stub = $this->stubFactory->create(
            function () use ($callMethod, $mock, $name) {
                return $callMethod->invoke(
                    $mock,
                    $name,
                    new Arguments(func_get_args())
                );
            },
            $mock
        );

        return $this->spyFactory->create($stub);
    }

    private static $instance;
    private $idSequencer;
    private $generator;
    private $hashGenerator;
    private $stubFactory;
    private $spyFactory;
    private $classes;
}
