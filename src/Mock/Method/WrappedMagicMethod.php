<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Method;

use Eloquent\Phony\Call\Argument\Arguments;
use Eloquent\Phony\Call\Argument\ArgumentsInterface;
use Eloquent\Phony\Invocation\AbstractWrappedInvocable;
use Eloquent\Phony\Mock\Handle\HandleInterface;
use Error;
use Exception;
use ReflectionMethod;

/**
 * A wrapper that allows calling of the parent magic method in mocks.
 */
class WrappedMagicMethod extends AbstractWrappedInvocable implements
    WrappedMethodInterface
{
    /**
     * Construct a new wrapped magic method.
     *
     * @param string           $name            The name.
     * @param ReflectionMethod $callMagicMethod The _callMagic() method.
     * @param boolean          $isUncallable    True if the underlying magic method is uncallable.
     * @param HandleInterface  $handle          The handle.
     */
    public function __construct(
        $name,
        ReflectionMethod $callMagicMethod,
        $isUncallable,
        HandleInterface $handle
    ) {
        $this->name = $name;
        $this->callMagicMethod = $callMagicMethod;
        $this->isUncallable = $isUncallable;
        $this->handle = $handle;

        if ($callMagicMethod->isStatic()) {
            $this->mock = null;
            $callback = array(
                $callMagicMethod->getDeclaringClass()->getName(),
                '__callStatic',
            );
        } else {
            $this->mock = $handle->mock();
            $callback = array($this->mock, '__call');
        }

        parent::__construct($callback);
    }

    /**
     * Get the method.
     *
     * @return ReflectionMethod The method.
     */
    public function callMagicMethod()
    {
        return $this->callMagicMethod;
    }

    /**
     * Returns true if uncallable.
     *
     * @return boolean True if uncallable.
     */
    public function isUncallable()
    {
        return $this->isUncallable;
    }

    /**
     * Get the name.
     *
     * @return string The name.
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * Get the handle.
     *
     * @return HandleInterface The handle.
     */
    public function handle()
    {
        return $this->handle;
    }

    /**
     * Get the mock.
     *
     * @return MockInterface|null The mock.
     */
    public function mock()
    {
        return $this->mock;
    }

    /**
     * Invoke this object.
     *
     * This method supports reference parameters.
     *
     * @param ArgumentsInterface|array $arguments The arguments.
     *
     * @return mixed           The result of invocation.
     * @throws Exception|Error If an error occurs.
     */
    public function invokeWith($arguments = array())
    {
        if ($this->isUncallable) {
            return;
        }

        if (!$arguments instanceof ArgumentsInterface) {
            $arguments = new Arguments($arguments);
        }

        return $this->callMagicMethod
            ->invoke($this->mock, $this->name, $arguments);
    }

    protected $name;
    protected $callMagicMethod;
    protected $isUncallable;
    protected $handle;
    protected $mock;
}
