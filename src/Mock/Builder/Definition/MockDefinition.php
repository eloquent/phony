<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Builder\Definition;

use Eloquent\Phony\Feature\FeatureDetector;
use Eloquent\Phony\Feature\FeatureDetectorInterface;
use Eloquent\Phony\Mock\Builder\Definition\Method\CustomMethodDefinition;
use Eloquent\Phony\Mock\Builder\Definition\Method\MethodDefinitionCollection;
use Eloquent\Phony\Mock\Builder\Definition\Method\MethodDefinitionCollectionInterface;
use Eloquent\Phony\Mock\Builder\Definition\Method\RealMethodDefinition;
use ReflectionClass;

/**
 * Represents a mock class definition.
 *
 * @internal
 */
class MockDefinition implements MockDefinitionInterface
{
    /**
     * Construct a new mock definition.
     *
     * @param array<string,ReflectionClass>|null $types                  The types.
     * @param array<string,callable|null>|null   $customMethods          The custom methods.
     * @param array<string,mixed>|null           $customProperties       The custom properties.
     * @param array<string,callable|null>|null   $customStaticMethods    The custom static methods.
     * @param array<string,mixed>|null           $customStaticProperties The custom static properties.
     * @param array<string,mixed>|null           $customConstants        The custom constants.
     * @param string|null                        $className              The class name.
     * @param string|null                        $id                     The identifier.
     * @param FeatureDetectorInterface|null      $featureDetector        The feature detector to use.
     */
    public function __construct(
        array $types = null,
        array $customMethods = null,
        array $customProperties = null,
        array $customStaticMethods = null,
        array $customStaticProperties = null,
        array $customConstants = null,
        $className = null,
        $id = null,
        FeatureDetectorInterface $featureDetector = null
    ) {
        if (null === $types) {
            $types = array();
        }
        if (null === $customMethods) {
            $customMethods = array();
        }
        if (null === $customProperties) {
            $customProperties = array();
        }
        if (null === $customStaticMethods) {
            $customStaticMethods = array();
        }
        if (null === $customStaticProperties) {
            $customStaticProperties = array();
        }
        if (null === $customConstants) {
            $customConstants = array();
        }
        if (null === $featureDetector) {
            $featureDetector = FeatureDetector::instance();
        }

        $this->types = $types;
        $this->customMethods = $customMethods;
        $this->customProperties = $customProperties;
        $this->customStaticMethods = $customStaticMethods;
        $this->customStaticProperties = $customStaticProperties;
        $this->customConstants = $customConstants;
        $this->className = $className;
        $this->id = $id;
        $this->featureDetector = $featureDetector;
    }

    /**
     * Get the types.
     *
     * @return array<string,ReflectionClass> The types.
     */
    public function types()
    {
        return $this->types;
    }

    /**
     * Get the custom methods.
     *
     * @return array<string,callable|null> The custom methods.
     */
    public function customMethods()
    {
        return $this->customMethods;
    }

    /**
     * Get the custom properties.
     *
     * @return array<string,mixed> The custom properties.
     */
    public function customProperties()
    {
        return $this->customProperties;
    }

    /**
     * Get the custom static methods.
     *
     * @return array<string,callable|null> The custom static methods.
     */
    public function customStaticMethods()
    {
        return $this->customStaticMethods;
    }

    /**
     * Get the custom static properties.
     *
     * @return array<string,mixed> The custom static properties.
     */
    public function customStaticProperties()
    {
        return $this->customStaticProperties;
    }

    /**
     * Get the custom constants.
     *
     * @return array<string,mixed> The custom constants.
     */
    public function customConstants()
    {
        return $this->customConstants;
    }

    /**
     * Get the class name.
     *
     * @return string The class name.
     */
    public function className()
    {
        $this->inspectTypes();

        return $this->className;
    }

    /**
     * Get the identifier.
     *
     * @return string|null The identifier.
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * Get the feature detector.
     *
     * @return FeatureDetectorInterface The feature detector.
     */
    public function featureDetector()
    {
        return $this->featureDetector;
    }

    /**
     * Get the type names.
     *
     * @return array<string> The type names.
     */
    public function typeNames()
    {
        $this->inspectTypes();

        return $this->typeNames;
    }

    /**
     * Get the parent class name.
     *
     * @return string|null The parent class name, or null if the mock will not extend a class.
     */
    public function parentClassName()
    {
        $this->inspectTypes();

        return $this->parentClassName;
    }

    /**
     * Get the interface names.
     *
     * @return array<string> The interface names.
     */
    public function interfaceNames()
    {
        $this->inspectTypes();

        return $this->interfaceNames;
    }

    /**
     * Get the trait names.
     *
     * @return array<string> The trait names.
     */
    public function traitNames()
    {
        $this->inspectTypes();

        return $this->traitNames;
    }

    /**
     * Get the method definitions.
     *
     * Calling this method will finalize the mock builder.
     *
     * @return MethodDefinitionCollectionInterface The method definitions.
     */
    public function methods()
    {
        $this->buildMethods();

        return $this->methods;
    }

    /**
     * Inspect the supplied types and build caches of useful information.
     */
    protected function inspectTypes()
    {
        if (null !== $this->typeNames) {
            return;
        }

        $this->typeNames = array();
        $this->interfaceNames = array();
        $this->traitNames = array();

        foreach ($this->types as $type) {
            $this->typeNames[] = $typeName = $type->getName();

            if ($type->isInterface()) {
                $this->interfaceNames[] = $typeName;
            } elseif (
                $this->featureDetector->isSupported('trait') &&
                $type->isTrait()
            ) {
                $this->traitNames[] = $typeName;
            } else {
                $this->parentClassName = $typeName;
            }
        }

        $this->buildClassName();
    }

    /**
     * Build the mock class name.
     *
     * @return string The mock class name.
     */
    protected function buildClassName()
    {
        if (null !== $this->className) {
            return;
        }

        $this->className = 'PhonyMock';

        if (null !== $this->parentClassName) {
            $subject = $this->parentClassName;
        } elseif ($this->interfaceNames) {
            $subject = $this->interfaceNames[0];
        } elseif ($this->traitNames) {
            $subject = $this->traitNames[0];
        } else {
            $subject = null;
        }

        if ($subject) {
            $subjectAtoms = preg_split('/[_\\\\]/', $subject);
            $this->className .= '_' . array_pop($subjectAtoms);
        }

        if (null === $this->id) {
            $this->className .= '_' . substr(md5(uniqid()), 0, 6);
        } else {
            $this->className .= '_' . $this->id;
        }
    }

    /**
     * Build the method definitions.
     */
    protected function buildMethods()
    {
        if (null !== $this->methods) {
            return;
        }

        $methods = array();
        $parameterCounts = array();

        foreach ($this->types as $type) {
            foreach ($type->getMethods() as $method) {
                $name = $method->getName();

                if ($this->isReservedWord($name)) {
                    continue;
                }

                if (
                    !$method->isFinal() &&
                    !$method->isPrivate() &&
                    !$method->isConstructor()
                ) {
                    $parameterCount = $method->getNumberOfParameters();

                    if (
                        !isset($methods[$name]) ||
                        $parameterCount > $parameterCounts[$name]
                    ) {
                        $methods[$name] = new RealMethodDefinition($method);
                        $parameterCounts[$name] = $parameterCount;
                    }
                }
            }
        }

        foreach ($this->customStaticMethods as $name => $callback) {
            $methods[$name] =
                new CustomMethodDefinition(true, $name, $callback);
        }

        foreach ($this->customMethods as $name => $callback) {
            $methods[$name] =
                new CustomMethodDefinition(false, $name, $callback);
        }

        ksort($methods, SORT_STRING);

        $this->methods = new MethodDefinitionCollection($methods);
    }

    /**
     * Determines if the supplied string is a reserved word.
     *
     * @param string $string The string.
     *
     * @return boolean True if the string is a reserved word.
     */
    protected function isReservedWord($string)
    {
        $tokens = token_get_all('<?php ' . $string);
        $token = $tokens[1];

        return !is_array($token) || $token[0] !== T_STRING;
    }

    private $types;
    private $customMethods;
    private $customProperties;
    private $customStaticMethods;
    private $customStaticProperties;
    private $customConstants;
    private $className;
    private $id;
    private $featureDetector;
    private $typeNames;
    private $parentClassName;
    private $interfaceNames;
    private $traitNames;
    private $methods;
}
