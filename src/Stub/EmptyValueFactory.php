<?php

declare(strict_types=1);

namespace Eloquent\Phony\Stub;

use Eloquent\Phony\Mock\Builder\MockBuilderFactory;
use Eloquent\Phony\Reflection\FeatureDetector;
use ReflectionFunctionAbstract;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

/**
 * Creates empty values from arbitrary types.
 */
class EmptyValueFactory
{
    /**
     * Get the static instance of this class.
     *
     * @return self The static instance.
     */
    public static function instance(): self
    {
        if (!self::$instance) {
            self::$instance = new self(
                FeatureDetector::instance()
            );
        }

        return self::$instance;
    }

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
     * @param ReflectionType $type The type.
     *
     * @return mixed A value of the supplied type.
     */
    public function fromType(ReflectionType $type)
    {
        if ($type->allowsNull()) {
            return null;
        }

        if ($type instanceof ReflectionNamedType) {
            $typeName = $type->getName();
        } elseif ($type instanceof ReflectionUnionType) {
            $subTypes = $type->getTypes();
            /** @var ReflectionNamedType */
            $lastSubType = end($subTypes);
            $typeName = $lastSubType->getName();
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
            return $this->fromType($type);
        }

        return null;
    }

    /**
     * @var ?self
     */
    private static $instance;

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
