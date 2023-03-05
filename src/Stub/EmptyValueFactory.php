<?php

declare(strict_types=1);

namespace Eloquent\Phony\Stub;

use Eloquent\Phony\Mock\Builder\MockBuilderFactory;
use Eloquent\Phony\Reflection\FeatureDetector;
use ReflectionFunctionAbstract;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

/**
 * Creates empty values from arbitrary types.
 */
class EmptyValueFactory
{
    /**
     * Construct a new empty value factory engine.
     *
     * @param FeatureDetector $featureDetector The feature detector to use.
     */
    public function __construct(FeatureDetector $featureDetector)
    {
        $this->isEnumSupported = $featureDetector->isSupported('type.enum');
    }

    /**
     * Set the stub verifier factory.
     *
     * @param StubVerifierFactory $stubVerifierFactory The stub verifier factory to use.
     */
    public function setStubVerifierFactory(
        StubVerifierFactory $stubVerifierFactory
    ): void {
        $this->stubVerifierFactory = $stubVerifierFactory;
    }

    /**
     * Set the mock builder factory.
     *
     * @param MockBuilderFactory $mockBuilderFactory The mock builder factory to use.
     */
    public function setMockBuilderFactory(
        MockBuilderFactory $mockBuilderFactory
    ): void {
        $this->mockBuilderFactory = $mockBuilderFactory;
    }

    /**
     * Create a value of the supplied type.
     *
     * @param ReflectionType $type        The type.
     * @param callable|null  $resolveSelf A callback to use if it is necessary to resolve a `self` or `static` type.
     *
     * @return mixed A value of the supplied type.
     */
    public function fromType(
        ReflectionType $type,
        callable $resolveSelf = null
    ) {
        if ($type->allowsNull()) {
            return null;
        }

        // in any union type, just use the last type
        if ($type instanceof ReflectionUnionType) {
            $subTypes = $type->getTypes();
            $type = end($subTypes);
        }

        if ($type instanceof ReflectionNamedType) {
            $typeName = $type->getName();
        } elseif ($type instanceof ReflectionIntersectionType) {
            // intersections can only be satisfied by a mock
            // also, they can only be composed of class/interface names
            $types = [];

            foreach ($type->getTypes() as $type) {
                $types[] = $type->getName();
            }

            return $this->mockBuilderFactory->create($types)->full();
        } else {
            return null;
        }

        switch (strtolower($typeName)) {
            case 'void':
                return null;

            case 'bool':
            case 'false':
                return false;

            case 'int':
                return 0;

            case 'float':
                return .0;

            case 'string':
                return '';

            case 'true':
                return true;

            case 'array':
            case 'iterable':
                return [];

            case 'object':
            case 'stdclass':
                return (object) [];

            case 'callable':
                return $this->stubVerifierFactory->create(null);

            case 'closure':
                return function () {};

            case 'generator':
                $fn = function () { yield from []; };

                return $fn();

            case 'self':
            case 'static':
                if ($resolveSelf) {
                    $typeName = $resolveSelf();
                }
        }

        if ($this->isEnumSupported && enum_exists($typeName)) {
            return $typeName::cases()[0] ?? null;
        }

        return $this->mockBuilderFactory->create($typeName)->full();
    }

    /**
     * Create a return value for the supplied function.
     *
     * @param ReflectionFunctionAbstract $function The function.
     *
     * @return mixed A value that can be returned by the function.
     */
    public function fromFunction(ReflectionFunctionAbstract $function)
    {
        if ($type = $function->getReturnType()) {
            if ($function instanceof ReflectionMethod) {
                return $this->fromType($type, function () use ($function) {
                    return $function->getDeclaringClass()->getName();
                });
            }

            return $this->fromType($type);
        }

        return null;
    }

    /**
     * @var bool
     */
    private $isEnumSupported;

    /**
     * @var StubVerifierFactory
     */
    private $stubVerifierFactory;

    /**
     * @var MockBuilderFactory
     */
    private $mockBuilderFactory;
}
