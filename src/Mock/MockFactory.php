<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock;

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Mock\Builder\MockDefinition;
use Eloquent\Phony\Mock\Exception\ClassExistsException;
use Eloquent\Phony\Mock\Exception\MockException;
use Eloquent\Phony\Mock\Exception\MockGenerationFailedException;
use Eloquent\Phony\Mock\Handle\HandleFactory;
use Eloquent\Phony\Sequencer\Sequencer;
use ParseError;
use ReflectionClass;

/**
 * Creates mock instances.
 */
class MockFactory
{
    /**
     * Construct a new mock factory.
     *
     * @param Sequencer     $labelSequencer The label sequencer to use.
     * @param MockGenerator $generator      The generator to use.
     * @param MockRegistry  $registry       The registry to use.
     * @param HandleFactory $handleFactory  The handle factory to use.
     */
    public function __construct(
        Sequencer $labelSequencer,
        MockGenerator $generator,
        MockRegistry $registry,
        HandleFactory $handleFactory
    ) {
        $this->labelSequencer = $labelSequencer;
        $this->generator = $generator;
        $this->registry = $registry;
        $this->handleFactory = $handleFactory;
        $this->definitions = [];
    }

    /**
     * Create the mock class for the supplied definition.
     *
     * @param MockDefinition $definition The definition.
     * @param bool           $createNew  True if a new class should be created even when a compatible one exists.
     *
     * @return ReflectionClass<object> The class.
     * @throws MockException           If the mock generation fails.
     */
    public function createMockClass(
        MockDefinition $definition,
        bool $createNew = false
    ): ReflectionClass {
        $signature = $definition->signature();

        if (!$createNew) {
            foreach ($this->definitions as $tuple) {
                if ($signature === $tuple[0]) {
                    return $tuple[1];
                }
            }
        }

        $className = $this->generator->generateClassName($definition);
        $isExisting = class_exists($className, false);

        if ($isExisting) {
            throw new ClassExistsException($className);
        }

        $source = $this->generator->generate($definition, $className);
        $reporting = error_reporting(E_ERROR | E_COMPILE_ERROR);

        try {
            eval($source);
        } catch (ParseError $e) {
            throw new MockGenerationFailedException(
                $className,
                $definition,
                $source,
                error_get_last(),
                $e
            );
        } finally {
            error_reporting($reporting);
        }

        $wasCreated = class_exists($className, false);

        if (!$wasCreated) {
            // @codeCoverageIgnoreStart
            throw new MockGenerationFailedException(
                $className,
                $definition,
                $source,
                error_get_last()
            );
            // @codeCoverageIgnoreEnd
        }

        /** @var class-string $normalizedClassName */
        $normalizedClassName = strtolower($className);
        $this->registry->definitions[$normalizedClassName] = $definition;
        $class = new ReflectionClass($className);
        $this->handleFactory->staticHandle($class);
        $this->definitions[] = [$signature, $class];

        return $class;
    }

    /**
     * Create a new full mock instance for the supplied class.
     *
     * @param ReflectionClass<object> $class The class.
     *
     * @return Mock          The newly created mock.
     * @throws MockException If the mock generation fails.
     */
    public function createFullMock(ReflectionClass $class): Mock
    {
        /** @var Mock */
        $mock = $class->newInstanceWithoutConstructor();
        $this->handleFactory
            ->instanceHandle($mock, strval($this->labelSequencer->next()));

        return $mock;
    }

    /**
     * Create a new partial mock instance for the supplied definition.
     *
     * @param ReflectionClass<object>         $class     The class.
     * @param Arguments|array<int,mixed>|null $arguments The constructor arguments, or null to bypass the constructor.
     *
     * @return Mock          The newly created mock.
     * @throws MockException If the mock generation fails.
     */
    public function createPartialMock(
        ReflectionClass $class,
        $arguments = []
    ): Mock {
        /** @var Mock */
        $mock = $class->newInstanceWithoutConstructor();
        $handle = $this->handleFactory
            ->instanceHandle($mock, strval($this->labelSequencer->next()));
        $handle->partial();

        if (null !== $arguments) {
            $handle->constructWith($arguments);
        }

        return $mock;
    }

    /**
     * @var Sequencer
     */
    private $labelSequencer;

    /**
     * @var MockGenerator
     */
    private $generator;

    /**
     * @var MockRegistry
     */
    private $registry;

    /**
     * @var HandleFactory
     */
    private $handleFactory;

    /**
     * @var array<int,array<mixed>>
     */
    private $definitions;
}
