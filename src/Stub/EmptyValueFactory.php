<?php

declare(strict_types=1);

namespace Eloquent\Phony\Stub;

use Eloquent\Phony\Mock\Builder\MockBuilderFactory;
use ReflectionFunctionAbstract;
use ReflectionType;

/**
 * Creates empty values from arbitrary types.
 */
class EmptyValueFactory
{
    /**
     * Get the static instance of this factory.
     *
     * @return EmptyValueFactory The static factory.
     */
    public static function instance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
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

        $typeName = $type->getName();

        switch (strtolower($typeName)) {
            case 'bool':
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
                return $this->stubVerifierFactory->create();

            case 'closure':
                return function () {};

            case 'generator':
                $fn = function () { return; yield; };

                return $fn();

            case 'void':
                return null;
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

    private static $instance;
    private $stubVerifierFactory;
    private $mockBuilderFactory;
}
