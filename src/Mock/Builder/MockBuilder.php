<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Builder;

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Mock\Exception\AnonymousClassException;
use Eloquent\Phony\Mock\Exception\FinalClassException;
use Eloquent\Phony\Mock\Exception\FinalizedMockException;
use Eloquent\Phony\Mock\Exception\InvalidClassNameException;
use Eloquent\Phony\Mock\Exception\InvalidDefinitionException;
use Eloquent\Phony\Mock\Exception\InvalidTypeException;
use Eloquent\Phony\Mock\Exception\MockException;
use Eloquent\Phony\Mock\Exception\MultipleInheritanceException;
use Eloquent\Phony\Mock\Handle\HandleFactory;
use Eloquent\Phony\Mock\Mock;
use Eloquent\Phony\Mock\MockFactory;
use Eloquent\Phony\Mock\MockGenerator;
use ReflectionClass;
use ReflectionException;
use ReflectionFunctionAbstract;

/**
 * Builds mock classes.
 */
class MockBuilder
{
    /**
     * The regular expression used to validate symbol names.
     */
    const SYMBOL_PATTERN = '[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*(?:\\\\[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*)*';

    /**
     * Construct a new mock builder.
     *
     * Each value in `$types` can be either a class name, or an ad hoc mock
     * definition. If only a single type is being mocked, the class name or
     * definition can be passed without being wrapped in an array.
     *
     * @param mixed              $types              The types to mock.
     * @param MockFactory        $factory            The factory to use.
     * @param HandleFactory      $handleFactory      The handle factory to use.
     * @param InvocableInspector $invocableInspector The invocable inspector.
     *
     * @throws MockException If invalid input is supplied.
     */
    public function __construct(
        $types,
        MockFactory $factory,
        HandleFactory $handleFactory,
        InvocableInspector $invocableInspector
    ) {
        $this->factory = $factory;
        $this->handleFactory = $handleFactory;
        $this->invocableInspector = $invocableInspector;

        $this->types = [];
        $this->parentClassName = '';
        $this->customMethods = [];
        $this->customProperties = [];
        $this->customStaticMethods = [];
        $this->customStaticProperties = [];
        $this->customConstants = [];
        $this->className = '';
        $this->isFinalized = false;
        $this->emptyCallback = function () {};

        if (null !== $types) {
            $this->like($types);
        }
    }

    /**
     * Clone this builder.
     */
    public function __clone()
    {
        $this->isFinalized = false;
        $this->definition = null;
        $this->class = null;
        $this->mock = null;
    }

    /**
     * Get the factory.
     *
     * @return MockFactory The factory.
     */
    public function factory(): MockFactory
    {
        return $this->factory;
    }

    /**
     * Get the handle factory.
     *
     * @return HandleFactory The handle factory.
     */
    public function handleFactory(): HandleFactory
    {
        return $this->handleFactory;
    }

    /**
     * Get the invocable inspector.
     *
     * @return InvocableInspector The invocable inspector.
     */
    public function invocableInspector(): InvocableInspector
    {
        return $this->invocableInspector;
    }

    /**
     * Get the types.
     *
     * @return array<string,ReflectionClass<object>> The types.
     */
    public function types(): array
    {
        return $this->types;
    }

    /**
     * Add classes, interfaces, or traits.
     *
     * Each value in `$types` can be either a class name, or an ad hoc mock
     * definition. If only a single type is being mocked, the class name or
     * definition can be passed without being wrapped in an array.
     *
     * @param mixed ...$types Types to add.
     *
     * @return $this         This builder.
     * @throws MockException If invalid input is supplied, or this builder is already finalized.
     */
    public function like(...$types): self
    {
        if ($this->isFinalized) {
            throw new FinalizedMockException();
        }

        $final = [];

        foreach ($types as $type) {
            if (is_array($type)) {
                if (!empty($type)) {
                    if (array_values($type) === $type) {
                        $final = array_merge($final, $type);
                    } else {
                        $final[] = $type;
                    }
                }
            } else {
                $final[] = $type;
            }
        }

        $toAdd = [];

        if (!$this->parentClassName) {
            $parentClassNames = [];
        } else {
            $parentClassNames = [$this->parentClassName];
        }

        $parentClassName = '';
        $definitions = [];

        foreach ($final as $type) {
            if (is_string($type)) {
                /** @var class-string $classString */
                $classString = $type;

                try {
                    $type = new ReflectionClass($classString);
                } catch (ReflectionException $e) {
                    throw new InvalidTypeException($type, $e);
                }
            } elseif (is_array($type)) {
                foreach ($type as $name => $value) {
                    if (!is_string($name)) {
                        throw new InvalidDefinitionException($name, $value);
                    }
                }

                $definitions[] = $type;

                continue;
            } else {
                throw new InvalidTypeException($type);
            }

            if ($type->isAnonymous()) {
                throw new AnonymousClassException();
            }

            $isTrait = $type->isTrait();

            if (!$isTrait && $type->isFinal()) {
                throw new FinalClassException($type->getName());
            }

            if (!$isTrait && !$type->isInterface()) {
                $parentClassNames[] = $parentClassName = $type->getName();
            }

            $toAdd[] = $type;
        }

        $parentClassNames = array_unique($parentClassNames);
        $parentClassCount = count($parentClassNames);

        if ($parentClassCount > 1) {
            throw new MultipleInheritanceException($parentClassNames);
        }

        foreach ($toAdd as $type) {
            $name = strtolower($type->getName());

            if (!isset($this->types[$name])) {
                $this->types[$name] = $type;
            }
        }

        if ($parentClassCount > 0) {
            $this->parentClassName = $parentClassName;
        }

        foreach ($definitions as $definition) {
            $this->define($definition);
        }

        return $this;
    }

    /**
     * Add a custom method.
     *
     * @param string    $name     The name.
     * @param ?callable $callback The callback.
     *
     * @return $this         This builder.
     * @throws MockException If this builder is already finalized.
     */
    public function addMethod(string $name, callable $callback = null): self
    {
        if ($this->isFinalized) {
            throw new FinalizedMockException();
        }
        if (!$callback) {
            $callback = $this->emptyCallback;
        }

        $this->customMethods[$name] = [
            $callback,
            $this->invocableInspector->callbackReflector($callback),
        ];

        return $this;
    }

    /**
     * Add a custom property.
     *
     * @param string  $name  The name.
     * @param mixed   $value The value.
     * @param ?string $type  The type.
     *
     * @return $this         This builder.
     * @throws MockException If this builder is already finalized.
     */
    public function addProperty(
        string $name,
        $value = null,
        string $type = null
    ): self {
        if ($this->isFinalized) {
            throw new FinalizedMockException();
        }

        $this->customProperties[$name] = [$type, $value];

        return $this;
    }

    /**
     * Add a custom static method.
     *
     * @param string    $name     The name.
     * @param ?callable $callback The callback.
     *
     * @return $this         This builder.
     * @throws MockException If this builder is already finalized.
     */
    public function addStaticMethod(
        string $name,
        callable $callback = null
    ): self {
        if ($this->isFinalized) {
            throw new FinalizedMockException();
        }
        if (!$callback) {
            $callback = $this->emptyCallback;
        }

        $this->customStaticMethods[$name] = [
            $callback,
            $this->invocableInspector->callbackReflector($callback),
        ];

        return $this;
    }

    /**
     * Add a custom static property.
     *
     * @param string  $name  The name.
     * @param mixed   $value The value.
     * @param ?string $type  The type.
     *
     * @return $this         This builder.
     * @throws MockException If this builder is already finalized.
     */
    public function addStaticProperty(
        string $name,
        $value = null,
        string $type = null
    ): self {
        if ($this->isFinalized) {
            throw new FinalizedMockException();
        }

        $this->customStaticProperties[$name] = [$type, $value];

        return $this;
    }

    /**
     * Add a custom class constant.
     *
     * @param string $name  The name.
     * @param mixed  $value The value.
     *
     * @return $this         This builder.
     * @throws MockException If this builder is already finalized.
     */
    public function addConstant(string $name, $value = null): self
    {
        if ($this->isFinalized) {
            throw new FinalizedMockException();
        }

        $this->customConstants[$name] = $value;

        return $this;
    }

    /**
     * Set the class name.
     *
     * @param string $className The class name, or empty string to use a generated name.
     *
     * @return $this         This builder.
     * @throws MockException If this builder is already finalized.
     */
    public function named(string $className): self
    {
        if ($this->isFinalized) {
            throw new FinalizedMockException();
        }

        if ('' !== $className) {
            if (
                !preg_match('/^' . static::SYMBOL_PATTERN . '$/S', $className)
            ) {
                throw new InvalidClassNameException($className);
            }
        }

        $this->className = $className;

        return $this;
    }

    /**
     * Returns true if this builder is finalized.
     *
     * @return bool True if finalized.
     */
    public function isFinalized(): bool
    {
        return $this->isFinalized;
    }

    /**
     * Finalize the mock builder.
     *
     * @return $this This builder.
     */
    public function finalize(): self
    {
        if (!$this->isFinalized) {
            $this->normalizeDefinition();
            $this->isFinalized = true;
            $this->definition = new MockDefinition(
                $this->types,
                $this->customMethods,
                $this->customProperties,
                $this->customStaticMethods,
                $this->customStaticProperties,
                $this->customConstants,
                $this->className
            );
        }

        return $this;
    }

    /**
     * Get the mock definitions.
     *
     * Calling this method will finalize the mock builder.
     *
     * @return MockDefinition The mock definition.
     */
    public function definition(): MockDefinition
    {
        $this->finalize();

        /** @var MockDefinition */
        return $this->definition;
    }

    /**
     * Returns true if the mock class has been built.
     *
     * @return bool True if the mock class has been built.
     */
    public function isBuilt(): bool
    {
        return (bool) $this->class;
    }

    /**
     * Generate and define the mock class.
     *
     * Calling this method will finalize the mock builder.
     *
     * @param bool $createNew True if a new class should be created even when a compatible one exists.
     *
     * @return ReflectionClass<object> The class.
     * @throws MockException           If the mock generation fails.
     */
    public function build(bool $createNew = false): ReflectionClass
    {
        if (!$this->class) {
            $this->class = $this->factory
                ->createMockClass($this->definition(), $createNew);
        }

        return $this->class;
    }

    /**
     * Generate and define the mock class, and return the class name.
     *
     * Calling this method will finalize the mock builder.
     *
     * @param bool $createNew True if a new class should be created even when a compatible one exists.
     *
     * @return string        The class name.
     * @throws MockException If the mock generation fails.
     */
    public function className(bool $createNew = false): string
    {
        return $this->build($createNew)->getName();
    }

    /**
     * Get a mock.
     *
     * This method will return the current mock, only creating a new mock if no
     * existing mock is available.
     *
     * If no existing mock is available, the created mock will be a full mock.
     *
     * Calling this method will finalize the mock builder.
     *
     * @return Mock          The mock instance.
     * @throws MockException If the mock generation fails.
     */
    public function get(): Mock
    {
        if ($this->mock) {
            return $this->mock;
        }

        $this->mock = $this->factory->createFullMock($this->build());

        return $this->mock;
    }

    /**
     * Create a new full mock.
     *
     * This method will always create a new mock, and will replace the current
     * mock.
     *
     * Calling this method will finalize the mock builder.
     *
     * @return Mock          The mock instance.
     * @throws MockException If the mock generation fails.
     */
    public function full(): Mock
    {
        $this->mock = $this->factory->createFullMock($this->build());

        return $this->mock;
    }

    /**
     * Create a new partial mock.
     *
     * This method will always create a new mock, and will replace the current
     * mock.
     *
     * Calling this method will finalize the mock builder.
     *
     * @param mixed ...$arguments The constructor arguments.
     *
     * @return Mock          The mock instance.
     * @throws MockException If the mock generation fails.
     */
    public function partial(...$arguments): Mock
    {
        $this->mock = $this->factory
            ->createPartialMock($this->build(), $arguments);

        return $this->mock;
    }

    /**
     * Create a new partial mock.
     *
     * This method will always create a new mock, and will replace the current
     * mock.
     *
     * Calling this method will finalize the mock builder.
     *
     * This method supports reference parameters.
     *
     * @param Arguments|array<int,mixed>|null $arguments The constructor arguments, or null to bypass the constructor.
     *
     * @return Mock          The mock instance.
     * @throws MockException If the mock generation fails.
     */
    public function partialWith($arguments = []): Mock
    {
        $this->mock =
            $this->factory->createPartialMock($this->build(), $arguments);

        return $this->mock;
    }

    /**
     * Get the generated source code of the mock class.
     *
     * Calling this method will finalize the mock builder.
     *
     * @param ?MockGenerator $generator The mock generator to use.
     *
     * @return string        The source code.
     * @throws MockException If the mock generation fails.
     */
    public function source(MockGenerator $generator = null): string
    {
        if (!$generator) {
            $generator = MockGenerator::instance();
        }

        return $generator->generate($this->definition());
    }

    private function normalizeDefinition(): void
    {
        $this->resolveInternalInterface(
            'traversable',
            'iterator',
            'iteratoraggregate'
        );
        $this->resolveInternalInterface(
            'datetimeinterface',
            'datetimeimmutable',
            'datetime'
        );
        $this->resolveInternalInterface('throwable', 'exception', 'error');
    }

    /**
     * @param class-string $interface
     * @param class-string $preferred
     * @param class-string $alternate
     */
    private function resolveInternalInterface(
        string $interface,
        string $preferred,
        string $alternate
    ): void {
        $isImplementor = false;
        $isConcrete = false;

        foreach ($this->types as $name => $type) {
            if (
                $preferred === $name ||
                $alternate === $name ||
                $type->isSubclassOf($preferred) ||
                $type->isSubclassOf($alternate)
            ) {
                $isConcrete = true;

                break;
            }

            if ($type->implementsInterface($interface)) {
                $isImplementor = true;

                if ($interface === $name) {
                    unset($this->types[$name]);
                } elseif ($type->isInternal()) {
                    $isConcrete = true;

                    break;
                }
            }
        }

        if ($isImplementor && !$isConcrete) {
            $this->types = array_merge(
                [$preferred => new ReflectionClass($preferred)],
                $this->types
            );
        }
    }

    /**
     * @param array<string,mixed> $definition
     */
    private function define(array $definition): self
    {
        foreach ($definition as $name => $value) {
            $nameParts = explode(' ', $name);
            /** @var string */
            $name = array_pop($nameParts);
            $isStatic = in_array('static', $nameParts);
            $isFunction = in_array('function', $nameParts);
            $isProperty = in_array('var', $nameParts);
            $isConstant = in_array('const', $nameParts);

            if (!$isFunction && !$isProperty && !$isConstant) {
                if (is_object($value) && is_callable($value)) {
                    $isFunction = true;
                }
            }

            if ($isFunction) {
                if ($isStatic) {
                    $this->addStaticMethod($name, $value);
                } else {
                    $this->addMethod($name, $value);
                }
            } elseif ($isConstant) {
                $this->addConstant($name, $value);
            } else {
                $type = array_pop($nameParts);

                if (!$type || 'static' === $type || 'var' === $type) {
                    $type = null;
                }

                if ($isStatic) {
                    $this->addStaticProperty($name, $value, $type);
                } else {
                    $this->addProperty($name, $value, $type);
                }
            }
        }

        return $this;
    }

    /**
     * @var MockFactory
     */
    private $factory;

    /**
     * @var HandleFactory
     */
    private $handleFactory;

    /**
     * @var InvocableInspector
     */
    private $invocableInspector;

    /**
     * @var array<string,ReflectionClass<object>>
     */
    private $types;

    /**
     * @var string
     */
    private $parentClassName;

    /**
     * @var array<string,array{0:callable,1:ReflectionFunctionAbstract}>
     */
    private $customMethods;

    /**
     * @var array<string,mixed>
     */
    private $customProperties;

    /**
     * @var array<string,array{0:callable,1:ReflectionFunctionAbstract}>
     */
    private $customStaticMethods;

    /**
     * @var array<string,mixed>
     */
    private $customStaticProperties;

    /**
     * @var array<string,mixed>
     */
    private $customConstants;

    /**
     * @var string
     */
    private $className;

    /**
     * @var bool
     */
    private $isFinalized;

    /**
     * @var callable
     */
    private $emptyCallback;

    /**
     * @var ?MockDefinition
     */
    private $definition;

    /**
     * @var ?ReflectionClass<object>
     */
    private $class;

    /**
     * @var ?Mock
     */
    private $mock;
}
