<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
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
use Eloquent\Phony\Mock\Handle\Factory\HandleFactory;
use Eloquent\Phony\Mock\Handle\Factory\HandleFactoryInterface;
use Eloquent\Phony\Mock\MockInterface;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Sequencer\SequencerInterface;
use ParseError;
use ParseException;
use ReflectionClass;

/**
 * Creates mock instances.
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
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Cosntruct a new mock factory.
     *
     * @param SequencerInterface|null     $labelSequencer The label sequencer to use.
     * @param MockGeneratorInterface|null $generator      The generator to use.
     * @param HandleFactoryInterface|null $handleFactory  The handle factory to use.
     */
    public function __construct(
        SequencerInterface $labelSequencer = null,
        MockGeneratorInterface $generator = null,
        HandleFactoryInterface $handleFactory = null
    ) {
        if (!$labelSequencer) {
            $labelSequencer = Sequencer::sequence('mock-label');
        }
        if (!$generator) {
            $generator = MockGenerator::instance();
        }
        if (!$handleFactory) {
            $handleFactory = HandleFactory::instance();
        }

        $this->labelSequencer = $labelSequencer;
        $this->generator = $generator;
        $this->handleFactory = $handleFactory;
        $this->definitions = array();
    }

    /**
     * Get the label sequencer.
     *
     * @return SequencerInterface The label sequencer.
     */
    public function labelSequencer()
    {
        return $this->labelSequencer;
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
     * Get the handle factory.
     *
     * @return HandleFactoryInterface The handle factory.
     */
    public function handleFactory()
    {
        return $this->handleFactory;
    }

    /**
     * Create the mock class for the supplied builder.
     *
     * @param MockBuilderInterface $builder   The builder.
     * @param boolean              $createNew True if a new class should be created even when a compatible one exists.
     *
     * @return ReflectionClass        The class.
     * @throws MockExceptionInterface If the mock generation fails.
     */
    public function createMockClass(
        MockBuilderInterface $builder,
        $createNew = false
    ) {
        $definition = $builder->definition();
        $signature = $definition->signature();

        if (!$createNew) {
            foreach ($this->definitions as $tuple) {
                if ($signature === $tuple[0]) {
                    return $tuple[1];
                }
            }
        }

        $className = $this->generator->generateClassName($definition);

        if (class_exists($className, false)) {
            throw new ClassExistsException($className);
        }

        $source = $this->generator->generate($definition, $className);

        $reporting = error_reporting(E_ERROR | E_COMPILE_ERROR);

        try {
            eval($source);
        } catch (ParseError $e) { // @codeCoverageIgnoreStart
            throw new MockGenerationFailedException(
                $definition,
                $source,
                error_get_last(),
                $e
            );
        } catch (ParseException $e) {
            throw new MockGenerationFailedException(
                $definition,
                $source,
                error_get_last(),
                $e
            );
        } // @codeCoverageIgnoreEnd

        error_reporting($reporting);

        if (!class_exists($className, false)) {
            throw new MockGenerationFailedException(
                $definition,
                $source,
                error_get_last()
            );
        }

        $class = new ReflectionClass($className);
        $customMethods = array();

        foreach ($definition->customStaticMethods() as $methodName => $method) {
            $customMethods[strtolower($methodName)] = $method;
        }
        foreach ($definition->customMethods() as $methodName => $method) {
            $customMethods[strtolower($methodName)] = $method;
        }

        $customMethodsProperty = $class->getProperty('_customMethods');
        $customMethodsProperty->setAccessible(true);
        $customMethodsProperty->setValue(null, $customMethods);

        $this->handleFactory->createStubbingStatic($class);

        $this->definitions[] = array($signature, $class);

        return $class;
    }

    /**
     * Create a new full mock instance for the supplied builder.
     *
     * @param MockBuilderInterface $builder The builder.
     *
     * @return MockInterface          The newly created mock.
     * @throws MockExceptionInterface If the mock generation fails.
     */
    public function createFullMock(MockBuilderInterface $builder)
    {
        $mock = $builder->build()->newInstanceArgs();
        $this->handleFactory
            ->createStubbing($mock, strval($this->labelSequencer->next()));

        return $mock;
    }

    /**
     * Create a new partial mock instance for the supplied builder.
     *
     * @param MockBuilderInterface          $builder   The builder.
     * @param ArgumentsInterface|array|null $arguments The constructor arguments, or null to bypass the constructor.
     *
     * @return MockInterface          The newly created mock.
     * @throws MockExceptionInterface If the mock generation fails.
     */
    public function createPartialMock(
        MockBuilderInterface $builder,
        $arguments = array()
    ) {
        $mock = $builder->build()->newInstanceArgs();
        $handle = $this->handleFactory
            ->createStubbing($mock, strval($this->labelSequencer->next()));

        $handle->partial();

        if (null !== $arguments) {
            $handle->constructWith($arguments);
        }

        return $mock;
    }

    private static $instance;
    private $labelSequencer;
    private $generator;
    private $handleFactory;
    private $definitions;
}
