<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Handle;

use Eloquent\Phony\Assertion\Recorder\AssertionRecorderInterface;
use Eloquent\Phony\Assertion\Renderer\AssertionRendererInterface;
use Eloquent\Phony\Call\Argument\Arguments;
use Eloquent\Phony\Call\Argument\ArgumentsInterface;
use Eloquent\Phony\Invocation\InvokerInterface;
use Eloquent\Phony\Mock\MockInterface;
use Eloquent\Phony\Stub\Factory\StubFactoryInterface;
use Eloquent\Phony\Stub\Factory\StubVerifierFactoryInterface;
use ReflectionClass;
use ReflectionObject;
use stdClass;

/**
 * An abstract base class for implementing instance handles.
 */
abstract class AbstractInstanceHandle extends AbstractHandle implements
    InstanceHandleInterface
{
    /**
     * Construct a new instance handle.
     *
     * @param MockInterface                $mock                The mock.
     * @param stdClass                     $state               The state.
     * @param StubFactoryInterface         $stubFactory         The stub factory to use.
     * @param StubVerifierFactoryInterface $stubVerifierFactory The stub verifier factory to use.
     * @param AssertionRendererInterface   $assertionRenderer   The assertion renderer to use.
     * @param AssertionRecorderInterface   $assertionRecorder   The assertion recorder to use.
     * @param InvokerInterface             $invoker             The invoker to use.
     */
    public function __construct(
        MockInterface $mock,
        stdClass $state,
        StubFactoryInterface $stubFactory,
        StubVerifierFactoryInterface $stubVerifierFactory,
        AssertionRendererInterface $assertionRenderer,
        AssertionRecorderInterface $assertionRecorder,
        InvokerInterface $invoker
    ) {
        $class = new ReflectionClass($mock);

        if ($class->hasMethod('_callParent')) {
            $callParentMethod = $class->getMethod('_callParent');
            $callParentMethod->setAccessible(true);
        } else {
            $callParentMethod = null;
        }

        if ($class->hasMethod('_callParentConstructor')) {
            $callParentConstructorMethod =
                $class->getMethod('_callParentConstructor');
            $callParentConstructorMethod->setAccessible(true);
        } else {
            $callParentConstructorMethod = null;
        }

        if ($class->hasMethod('_callTrait')) {
            $callTraitMethod = $class->getMethod('_callTrait');
            $callTraitMethod->setAccessible(true);
        } else {
            $callTraitMethod = null;
        }

        if ($class->hasMethod('_callMagic')) {
            $callMagicMethod = $class->getMethod('_callMagic');
            $callMagicMethod->setAccessible(true);
        } else {
            $callMagicMethod = null;
        }

        $this->callParentConstructorMethod = $callParentConstructorMethod;
        $this->isAdaptable = true;

        parent::__construct(
            $class,
            $state,
            $callParentMethod,
            $callTraitMethod,
            $callMagicMethod,
            $mock,
            $stubFactory,
            $stubVerifierFactory,
            $assertionRenderer,
            $assertionRecorder,
            $invoker
        );
    }

    /**
     * Get the mock.
     *
     * @return MockInterface The mock.
     */
    public function mock()
    {
        return $this->mock;
    }

    /**
     * Call the original constructor.
     *
     * @param mixed ...$arguments The arguments.
     *
     * @return $this This handle.
     */
    public function construct()
    {
        return $this->constructWith(func_get_args());
    }

    /**
     * Call the original constructor.
     *
     * @param ArgumentsInterface|array $arguments The arguments.
     *
     * @return $this This handle.
     */
    public function constructWith($arguments = array())
    {
        if ($this->callParentConstructorMethod) {
            if (!$arguments instanceof ArgumentsInterface) {
                $arguments = new Arguments($arguments);
            }

            $this->callParentConstructorMethod->invoke($this->mock, $arguments);
        }

        return $this;
    }

    /**
     * Set the label.
     *
     * @param string|null $label The label.
     *
     * @return $this This handle.
     */
    public function setLabel($label)
    {
        $this->state->label = $label;

        return $this;
    }

    /**
     * Get the label.
     *
     * @return string|null The label.
     */
    public function label()
    {
        return $this->state->label;
    }

    /**
     * Use the supplied object as the implementation for all methods of the
     * mock.
     *
     * This method may help when partial mocking of a particular implementation
     * is not possible; as in the case of a final class.
     *
     * @param object $object The object to use.
     *
     * @return $this This handle.
     */
    public function proxy($object)
    {
        $reflector = new ReflectionObject($object);

        foreach ($reflector->getMethods() as $method) {
            if (
                $method->isStatic() ||
                $method->isPrivate() ||
                $method->isConstructor() ||
                $method->isDestructor()
            ) {
                continue;
            }

            $name = $method->getName();

            if ($this->class->hasMethod($name)) {
                $method->setAccessible(true);

                $this->stub($name)->doesWith(
                    function ($arguments) use ($method, $object) {
                        return $method->invokeArgs($object, $arguments->all());
                    },
                    array(),
                    false,
                    true,
                    false
                );
            }
        }

        return $this;
    }

    /**
     * Set whether this handle should be adapted to its mock automatically.
     *
     * @param boolean $isAdaptable True if this handle should be adapted automatically.
     *
     * @return $this This handle.
     */
    public function setIsAdaptable($isAdaptable)
    {
        $this->isAdaptable = $isAdaptable;

        return $this;
    }

    /**
     * Returns true if this handle should be adapted to its mock automatically.
     *
     * @return boolean True if this handle should be adapted automatically.
     */
    public function isAdaptable()
    {
        return $this->isAdaptable;
    }

    private $callParentConstructorMethod;
    private $isAdaptable;
}
