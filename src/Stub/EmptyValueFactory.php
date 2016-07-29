<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub;

use Eloquent\Phony\Mock\Builder\MockBuilderFactory;
use ReflectionClass;
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
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Construct a new empty value factory.
     */
    public function __construct()
    {
        $functionReflector = new ReflectionClass('ReflectionFunction');
        $this->isReturnTypeSupported =
            $functionReflector->hasMethod('getReturnType');
    }

    public function setStubVerifierFactory(
        StubVerifierFactory $stubVerifierFactory
    ) {
        $this->stubVerifierFactory = $stubVerifierFactory;
    }

    public function setMockBuilderFactory(
        MockBuilderFactory $mockBuilderFactory
    ) {
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

        $typeName = strval($type);

        switch (strtolower($typeName)) {
            case 'bool':
            case 'hh\bool':
                return false;

            case 'int':
            case 'hh\int':
                return 0;

            case 'float':
            case 'hh\float':
                return .0;

            case 'string':
            case 'hh\string':
                return '';

            case 'array':
                return array();

            case 'stdclass':
                return (object) array();

            case 'callable':
                return $this->stubVerifierFactory->create();

            case 'closure':
                return function () {};

            case 'generator':
                $fn = function () { return; yield; };

                return $fn();

            case 'hh\mixed':
                return null;
        }

        return $this->mockBuilderFactory->create($typeName)->full();
    }

    private static $instance;
    private $stubVerifierFactory;
    private $mockBuilderFactory;
    private $isReturnTypeSupported;
}
