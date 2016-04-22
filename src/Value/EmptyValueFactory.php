<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Value;

use Eloquent\Phony\Mock\Factory\MockFactory;
use Eloquent\Phony\Mock\Handle\Factory\HandleFactory;
use EmptyIterator;
use ReflectionClass;
use ReflectionFunctionAbstract;
use ReflectionParameter;
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
            self::$instance =
                new self(MockFactory::instance(), HandleFactory::instance());
        }

        return self::$instance;
    }

    /**
     * Construct a new empty value factory.
     *
     * @param MockFactory   $mockFactory   The mock factory to use.
     * @param HandleFactory $handleFactory The handle factory to use.
     */
    public function __construct(
        MockFactory $mockFactory,
        HandleFactory $handleFactory
    ) {
        $this->mockFactory = $mockFactory;
        $this->handleFactory = $handleFactory;

        $parameterReflector = new ReflectionClass('ReflectionParameter');
        $this->isCallableTypeHintSupported =
            $parameterReflector->hasMethod('isCallable');
        $this->isParameterTypeSupported =
            $parameterReflector->hasMethod('getType');

        $functionReflector = new ReflectionClass('ReflectionFunction');
        $this->isReturnTypeSupported =
            $functionReflector->hasMethod('getReturnType');
    }

    /**
     * Create a value of the supplied type.
     *
     * @param string $typeName The type name.
     *
     * @return mixed A value of the supplied type.
     */
    public function fromTypeName($typeName)
    {
        switch (strtolower($typeName)) {
            case 'null': return null;
            case 'bool':
            case 'boolean': return false;
            case 'int':
            case 'integer': return 0;
            case 'double':
            case 'float': return .0;
            case 'string': return '';
            case 'array': return array();
            case 'callable': return function () {};
            case 'resource': return fopen('data://text/plain,');
            case 'stdclass': return (object) array();

            case 'traversable':
            case 'iterator':
                return new EmptyIterator();

            case 'generator':
                $fn = function () { return; yield; };

                return $fn();
        }

        $builder = new MockBuilder(
            $typeName,
            $this->mockFactory,
            $this->handleFactory
        );

        return $builder->full();
    }

    /**
     * Create a value that would be accepted by the supplied parameter.
     *
     * @param ReflectionParameter $paramter The parameter.
     *
     * @return mixed A value of the parameter type.
     */
    public function fromParameter(ReflectionParameter $parameter)
    {
        if ($parameter->isDefaultValueAvailable($parameter)) {
            return $parameter->getDefaultValue();
        }

        if ($this->isParameterTypeSupported) {
            if (!$type = $parameter->getType()) {
                return null;
            }

            return $this->fromType($type);
        }

        if ($typeName = $parameter->getClass()) {
            switch (strtolower($typeName)) {
                case 'stdclass': return (object) array();

                case 'traversable':
                case 'iterator':
                    return new EmptyIterator();

                case 'generator':
                    $fn = function () { return; yield; };

                    return $fn();
            }

            $builder = new MockBuilder(
                $typeName,
                $this->mockFactory,
                $this->handleFactory
            );

            return $builder->full();
        }

        if ($parameter->isArray()) {
            return array();
        }

        if ($this->isCallableTypeHintSupported && $parameter->isCallable()) {
            return function () {};
        }

        return null;
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
            case 'bool': return false;
            case 'int': return 0;
            case 'float': return .0;
            case 'string': return '';
            case 'array': return array();
            case 'callable': return function () {};
            case 'stdclass': return (object) array();

            case 'traversable':
            case 'iterator':
                return new EmptyIterator();

            case 'generator':
                $fn = function () { return; yield; };

                return $fn();
        }

        $builder = new MockBuilder(
            $typeName,
            $this->mockFactory,
            $this->handleFactory
        );

        return $builder->full();
    }

    /**
     * Create a value that can be returned by the supplied function.
     *
     * @param ReflectionFunctionAbstract $function The function.
     *
     * @return mixed A value of the return type.
     */
    public function fromReturnType(ReflectionFunctionAbstract $function)
    {
        if (
            $this->isReturnTypeSupported &&
            $type = $function->getReturnType()
        ) {
            return $this->fromType($type);
        }

        return null;
    }

    private static $instance;
    private $mockFactory;
    private $handleFactory;
    private $isCallableTypeHintSupported;
    private $isParameterTypeSupported;
    private $isReturnTypeSupported;
}
