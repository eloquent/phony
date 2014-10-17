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

use Eloquent\Phony\Mock\Builder\Definition\Method\MethodDefinitionInterface;
use Eloquent\Phony\Mock\Builder\MockBuilderInterface;
use Eloquent\Phony\Mock\Generator\MockGenerator;
use Eloquent\Phony\Mock\Generator\MockGeneratorInterface;
use Eloquent\Phony\Mock\Method\WrappedMethod;
use Eloquent\Phony\Mock\MockInterface;
use Eloquent\Phony\Stub\Factory\StubVerifierFactory;
use Eloquent\Phony\Stub\Factory\StubVerifierFactoryInterface;
use Eloquent\Phony\Stub\StubVerifierInterface;
use ReflectionClass;
use RuntimeException;

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
     * @param MockGeneratorInterface|null       $generator           The generator to use.
     * @param StubVerifierFactoryInterface|null $stubVerifierFactory The stub verifier factory to use.
     */
    public function __construct(
        MockGeneratorInterface $generator = null,
        StubVerifierFactoryInterface $stubVerifierFactory = null
    ) {
        if (null === $generator) {
            $generator = MockGenerator::instance();
        }
        if (null === $stubVerifierFactory) {
            $stubVerifierFactory = StubVerifierFactory::instance();
        }

        $this->generator = $generator;
        $this->stubVerifierFactory = $stubVerifierFactory;
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
     * Get the stub verifier factory.
     *
     * @return StubVerifierFactoryInterface The stub verifier factory.
     */
    public function stubVerifierFactory()
    {
        return $this->stubVerifierFactory;
    }

    /**
     * Create the mock class for the supplied builder.
     *
     * @param MockBuilderInterface $builder The builder.
     *
     * @return ReflectionClass  The class.
     * @throws RuntimeException If the mock generation fails.
     */
    public function createMockClass(MockBuilderInterface $builder)
    {
        $builder->finalize();

        $className = $builder->className();
        $isNew = !class_exists($className, false);

        if ($isNew) {
            $source = $this->generator->generate($builder);

            @eval($source);

            if (!class_exists($className, false)) {
                if (defined('HHVM_VERSION')) { // @codeCoverageIgnoreStart
                    throw new RuntimeException('Mock class generation failed.');
                } // @codeCoverageIgnoreEnd

                $error = error_get_last();
                $errorLineNumber = $error['line'];
                $lines = explode("\n", $source);

                $startLine = $errorLineNumber - 4;
                $contextLineCount = 7;

                if ($startLine < 0) {
                    $contextLineCount += $startLine;
                    $startLine = 0;
                }

                $lines =
                    array_slice($lines, $startLine, $contextLineCount, true);
                $renderedLines = '';

                foreach ($lines as $lineNumber => $line) {
                    $renderedLines .= sprintf(
                        "\n%s: %s",
                        str_pad($lineNumber + 1, 8, ' ', STR_PAD_LEFT),
                        $line
                    );
                }

                throw new RuntimeException(
                    sprintf(
                        "Mock class generation failed: " .
                            "%s in generated code on line %d.\n" .
                            "Relevant lines:%s",
                        $error['message'],
                        $errorLineNumber,
                        $renderedLines
                    )
                );
            }
        }

        $class = new ReflectionClass($className);

        if ($isNew) {
            $property = $class->getProperty('_staticStubs');
            $property->setAccessible(true);
            $property->setValue(
                null,
                $this->createStubs(
                    $class,
                    $builder->methodDefinitions()->staticMethods()
                )
            );
        }

        return $class;
    }

    /**
     * Create a new mock instance for the supplied builder.
     *
     * @param MockBuilderInterface      $builder   The builder.
     * @param array<integer,mixed>|null $arguments The constructor arguments, or null to bypass the constructor.
     *
     * @return MockInterface    The newly created mock.
     * @throws RuntimeException If the mock generation fails.
     */
    public function createMock(
        MockBuilderInterface $builder,
        array $arguments = null
    ) {
        $class = $this->createMockClass($builder);
        $mock = $class->newInstanceArgs();

        $property = $class->getProperty('_stubs');
        $property->setAccessible(true);
        $property->setValue(
            $mock,
            $this->createStubs(
                $class,
                $builder->methodDefinitions()->methods(),
                $mock
            )
        );

        if (null !== $arguments) {
            if ($parentClass = $class->getParentClass()) {
                if ($constructor = $parentClass->getConstructor()) {
                    $method = $class->getMethod('_callParent');
                    $method->setAccessible(true);
                    $method->invoke($mock, $constructor->getName(), $arguments);
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
     * @return array<string,StubVerifierInterface> The stubs.
     */
    protected function createStubs(
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
            $name = $method->name();

            if ($method->isCustom()) {
                $stubs[$name] = $stub = $this->stubVerifierFactory
                    ->createFromCallback($method->callback(), $mock);
            } else {
                if ($method->isStatic()) {
                    $callParentMethod = $callParentStatic;
                } else {
                    $callParentMethod = $callParentInstance;
                }

                $stubs[$name] = $stub = $this->stubVerifierFactory
                    ->createFromCallback(
                        new WrappedMethod(
                            $callParentMethod,
                            $method->method(),
                            $mock
                        ),
                        $mock
                    );
            }

            $stub->forwards();
        }

        return $stubs;
    }

    private static $instance;
    private $generator;
    private $stubVerifierFactory;
}
