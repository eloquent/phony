<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Builder;

use Eloquent\Phony\Mock\Builder\Method\CustomMethodDefinition;
use Eloquent\Phony\Mock\Builder\Method\MethodDefinitionCollection;
use Eloquent\Phony\Mock\Builder\Method\RealMethodDefinition;
use Eloquent\Phony\Mock\Builder\Method\TraitMethodDefinition;
use ReflectionClass;
use ReflectionFunctionAbstract;

/**
 * Represents a mock class definition.
 */
class MockDefinition
{
    /**
     * Construct a new mock definition.
     *
     * @param array<string,ReflectionClass<object>>                        $types                  The types.
     * @param array<string,array{0:callable,1:ReflectionFunctionAbstract}> $customMethods          The custom methods.
     * @param array<string,mixed>                                          $customProperties       The custom properties.
     * @param array<string,array{0:callable,1:ReflectionFunctionAbstract}> $customStaticMethods    The custom static methods.
     * @param array<string,mixed>                                          $customStaticProperties The custom static properties.
     * @param array<string,mixed>                                          $customConstants        The custom constants.
     * @param string                                                       $className              The class name.
     */
    public function __construct(
        array $types,
        array $customMethods,
        array $customProperties,
        array $customStaticMethods,
        array $customStaticProperties,
        array $customConstants,
        string $className
    ) {
        $this->types = $types;
        $this->customMethods = $customMethods;
        $this->customProperties = $customProperties;
        $this->customStaticMethods = $customStaticMethods;
        $this->customStaticProperties = $customStaticProperties;
        $this->customConstants = $customConstants;
        $this->className = $className;
        $this->parentClassName = '';

        $this->signature = [
            'types' => array_keys($types),
            'customMethods' => [],
            'customProperties' => $customProperties,
            'customStaticMethods' => [],
            'customStaticProperties' => $customStaticProperties,
            'customConstants' => $customConstants,
            'className' => $className,
        ];

        foreach ($customMethods as $name => $method) {
            list(, $reflector) = $method;

            $this->signature['customMethods'][$name] = [
                'custom',
                $reflector->getFileName(),
                $reflector->getStartLine(),
                $reflector->getEndLine(),
            ];
        }

        foreach ($customStaticMethods as $name => $method) {
            list(, $reflector) = $method;

            $this->signature['customStaticMethods'][$name] = [
                'custom',
                $reflector->getFileName(),
                $reflector->getStartLine(),
                $reflector->getEndLine(),
            ];
        }
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
     * Get the custom methods.
     *
     * @return array<string,array{0:callable,1:ReflectionFunctionAbstract}> The custom methods.
     */
    public function customMethods(): array
    {
        return $this->customMethods;
    }

    /**
     * Get the custom properties.
     *
     * @return array<string,mixed> The custom properties.
     */
    public function customProperties(): array
    {
        return $this->customProperties;
    }

    /**
     * Get the custom static methods.
     *
     * @return array<string,array{0:callable,1:ReflectionFunctionAbstract}> The custom static methods.
     */
    public function customStaticMethods(): array
    {
        return $this->customStaticMethods;
    }

    /**
     * Get the custom static properties.
     *
     * @return array<string,mixed> The custom static properties.
     */
    public function customStaticProperties(): array
    {
        return $this->customStaticProperties;
    }

    /**
     * Get the custom constants.
     *
     * @return array<string,mixed> The custom constants.
     */
    public function customConstants(): array
    {
        return $this->customConstants;
    }

    /**
     * Get the class name.
     *
     * @return string The class name.
     */
    public function className(): string
    {
        return $this->className;
    }

    /**
     * Get the signature.
     *
     * This is an opaque value designed to aid in determining whether two mock
     * definitions are the same.
     *
     * @return mixed The signature.
     */
    public function signature()
    {
        return $this->signature;
    }

    /**
     * Get the type names.
     *
     * @return array<int,string> The type names.
     */
    public function typeNames(): array
    {
        $this->inspectTypes();

        return $this->typeNames;
    }

    /**
     * Get the parent class name.
     *
     * @return string The parent class name, or an empty string if the mock will not extend a class.
     */
    public function parentClassName(): string
    {
        $this->inspectTypes();

        return $this->parentClassName;
    }

    /**
     * Get the interface names.
     *
     * @return array<int,string> The interface names.
     */
    public function interfaceNames(): array
    {
        $this->inspectTypes();

        return $this->interfaceNames;
    }

    /**
     * Get the trait names.
     *
     * @return array<int,string> The trait names.
     */
    public function traitNames(): array
    {
        $this->inspectTypes();

        return $this->traitNames;
    }

    /**
     * Get the method definitions.
     *
     * Calling this method will finalize the mock builder.
     *
     * @return MethodDefinitionCollection The method definitions.
     */
    public function methods(): MethodDefinitionCollection
    {
        $this->buildMethods();

        return $this->methods;
    }

    /**
     * Check if the supplied definition is equal to this definition.
     *
     * @return bool True if equal.
     */
    public function isEqualTo(self $definition): bool
    {
        return $definition->signature() === $this->signature;
    }

    private function inspectTypes(): void
    {
        if (null !== $this->typeNames) {
            return;
        }

        $this->typeNames = [];
        $this->interfaceNames = [];
        $this->traitNames = [];

        foreach ($this->types as $type) {
            $this->typeNames[] = $typeName = $type->getName();

            if ($type->isInterface()) {
                $this->interfaceNames[] = $typeName;
            } elseif ($type->isTrait()) {
                $this->traitNames[] = $typeName;
            } else {
                $this->parentClassName = $typeName;
            }
        }
    }

    private function buildMethods(): void
    {
        if (null !== $this->methods) {
            return;
        }

        $methods = [];
        $unmockable = [];

        if ($typeName = $this->parentClassName()) {
            $type = $this->types[strtolower($typeName)];

            foreach ($type->getMethods() as $method) {
                if ($method->isPrivate()) {
                    continue;
                }

                $methodName = $method->getName();

                if ($method->isConstructor() || $method->isFinal()) {
                    $unmockable[$methodName] = true;
                } else {
                    $methods[$methodName] =
                        new RealMethodDefinition($method, $methodName);
                }
            }
        }

        $traitMethods = [];

        foreach ($this->traitNames() as $typeName) {
            $type = $this->types[strtolower($typeName)];

            foreach ($type->getMethods() as $method) {
                $methodName = $method->getName();
                $methodDefinition =
                    new TraitMethodDefinition($method, $methodName);

                if (!$method->isAbstract()) {
                    $traitMethods[] = $methodDefinition;
                }

                if (isset($unmockable[$methodName])) {
                    continue;
                }

                if (!isset($methods[$methodName])) {
                    $methods[$methodName] = $methodDefinition;
                }
            }
        }

        foreach ($this->interfaceNames() as $typeName) {
            $type = $this->types[strtolower($typeName)];

            foreach ($type->getMethods() as $method) {
                $methodName = $method->getName();

                if (isset($unmockable[$methodName])) {
                    continue;
                }

                if (!isset($methods[$methodName])) {
                    $methods[$methodName] =
                        new RealMethodDefinition($method, $methodName);
                }
            }
        }

        unset($methods['class']);

        foreach ($this->customStaticMethods as $methodName => $method) {
            list($callback, $reflector) = $method;

            $methods[$methodName] = new CustomMethodDefinition(
                true,
                $methodName,
                $callback,
                $reflector
            );
        }

        foreach ($this->customMethods as $methodName => $method) {
            list($callback, $reflector) = $method;

            $methods[$methodName] = new CustomMethodDefinition(
                false,
                $methodName,
                $callback,
                $reflector
            );
        }

        $this->methods =
            new MethodDefinitionCollection($methods, $traitMethods);
    }

    /**
     * @var array<string,ReflectionClass<object>>
     */
    private $types;

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
     * @var mixed
     */
    private $signature;

    /**
     * @var array<int,string>
     */
    private $typeNames;

    /**
     * @var string
     */
    private $parentClassName;

    /**
     * @var array<int,string>
     */
    private $interfaceNames;

    /**
     * @var array<int,string>
     */
    private $traitNames;

    /**
     * @var MethodDefinitionCollection
     */
    private $methods;
}
