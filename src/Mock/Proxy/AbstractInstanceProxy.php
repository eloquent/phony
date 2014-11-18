<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Proxy;

use Eloquent\Phony\Assertion\Recorder\AssertionRecorderInterface;
use Eloquent\Phony\Assertion\Renderer\AssertionRendererInterface;
use Eloquent\Phony\Call\Argument\Arguments;
use Eloquent\Phony\Call\Argument\ArgumentsInterface;
use Eloquent\Phony\Matcher\WildcardMatcherInterface;
use Eloquent\Phony\Mock\MockInterface;
use Eloquent\Phony\Stub\Factory\StubFactoryInterface;
use Eloquent\Phony\Stub\Factory\StubVerifierFactoryInterface;
use ReflectionClass;
use stdClass;

/**
 * An abstract base class for implementing instance proxies.
 *
 * @internal
 */
abstract class AbstractInstanceProxy extends AbstractProxy implements
    InstanceProxyInterface
{
    /**
     * Construct a new instance proxy.
     *
     * @param MockInterface                     $mock                The mock.
     * @param stdClass|null                     $state               The state.
     * @param StubFactoryInterface|null         $stubFactory         The stub factory to use.
     * @param StubVerifierFactoryInterface|null $stubVerifierFactory The stub verifier factory to use.
     * @param AssertionRendererInterface|null   $assertionRenderer   The assertion renderer to use.
     * @param AssertionRecorderInterface|null   $assertionRecorder   The assertion recorder to use.
     * @param WildcardMatcherInterface|null     $wildcardMatcher     The wildcard matcher to use.
     */
    public function __construct(
        MockInterface $mock,
        stdClass $state = null,
        StubFactoryInterface $stubFactory = null,
        StubVerifierFactoryInterface $stubVerifierFactory = null,
        AssertionRendererInterface $assertionRenderer = null,
        AssertionRecorderInterface $assertionRecorder = null,
        WildcardMatcherInterface $wildcardMatcher = null
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

        $this->mock = $mock;
        $this->class = $class;
        $this->callParentConstructorMethod = $callParentConstructorMethod;

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
            $wildcardMatcher
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
     * @param mixed $arguments,... The arguments.
     *
     * @return ProxyInterface This proxy.
     */
    public function construct()
    {
        return $this->constructWith(func_get_args());
    }

    /**
     * Call the original constructor.
     *
     * @param ArgumentsInterface|array<integer,mixed>|null $arguments The arguments.
     *
     * @return ProxyInterface This proxy.
     */
    public function constructWith($arguments = null)
    {
        if ($this->callParentConstructorMethod) {
            $this->callParentConstructorMethod
                ->invoke($this->mock, Arguments::adapt($arguments));
        }

        return $this;
    }

    /**
     * Set the label.
     *
     * @param string|null $label The label.
     */
    public function setLabel($label)
    {
        $this->state->label = $label;
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

    private $mock;
    private $class;
    private $callParentConstructorMethod;
}
