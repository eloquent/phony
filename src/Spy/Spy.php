<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy;

use Eloquent\Phony\Call\Call;
use Eloquent\Phony\Call\CallInterface;
use Eloquent\Phony\Call\Factory\CallFactory;
use Eloquent\Phony\Call\Factory\CallFactoryInterface;
use Eloquent\Phony\Invocable\AbstractInvocable;
use Exception;
use InvalidArgumentException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;

/**
 * Spies on a function or method.
 *
 * @internal
 */
class Spy extends AbstractInvocable implements SpyInterface
{
    /**
     * Construct a new spy.
     *
     * @param callable|null                   $subject     The subject, or null to create an unbound spy.
     * @param ReflectionFunctionAbstract|null $reflector   The reflector to use.
     * @param CallFactoryInterface|null       $callFactory The call factory to use.
     *
     * @throws InvalidArgumentException If the supplied subject is not supported.
     */
    public function __construct(
        $subject = null,
        ReflectionFunctionAbstract $reflector = null,
        CallFactoryInterface $callFactory = null
    ) {
        if (null === $subject) {
            $subject = function () {};
        }
        if (null === $reflector) {
            $reflector = $this->reflectorByCallBack($subject);
        }
        if (null === $callFactory) {
            $callFactory = CallFactory::instance();
        }

        $this->subject = $subject;
        $this->reflector = $reflector;
        $this->callFactory = $callFactory;
        $this->calls = array();
    }

    /**
     * Get the call factory.
     *
     * @return CallFactoryInterface The call factory.
     */
    public function callFactory()
    {
        return $this->callFactory;
    }

    /**
     * Get the subject.
     *
     * @return callable The subject.
     */
    public function subject()
    {
        return $this->subject;
    }

    /**
     * Get the reflector.
     *
     * @return ReflectionFunctionAbstract The reflector.
     */
    public function reflector()
    {
        return $this->reflector;
    }

    /**
     * Set the calls.
     *
     * @param array<CallInterface> $calls The calls.
     */
    public function setCalls(array $calls)
    {
        $this->calls = $calls;
    }

    /**
     * Add a call.
     *
     * @param CallInterface $call The call.
     */
    public function addCall(CallInterface $call)
    {
        $this->calls[] = $call;
    }

    /**
     * Get the calls.
     *
     * @return array<CallInterface> The calls.
     */
    public function calls()
    {
        return $this->calls;
    }

    /**
     * Invoke this object.
     *
     * This method supports reference parameters.
     *
     * @param array<integer,mixed>|null The arguments.
     *
     * @return mixed     The result of invocation.
     * @throws Exception If an error occurs.
     */
    public function invokeWith(array $arguments = null)
    {
        $this->calls[] = $call = $this->callFactory
            ->record($this->subject, $arguments);

        $exception = $call->exception();

        if ($exception) {
            throw $exception;
        }

        return $call->returnValue();
    }

    /**
     * Get the appropriate reflector for the supplied callback.
     *
     * @param callable $callback The callback.
     *
     * @return ReflectionFunctionAbstract The reflector.
     * @throws InvalidArgumentException   If the supplied callback is invalid.
     */
    protected function reflectorByCallBack($callback)
    {
        if (!is_callable($callback)) {
            throw new InvalidArgumentException('Unsupported callback.');
        }

        if (is_array($callback)) {
            return new ReflectionMethod($callback[0], $callback[1]);
        }

        if (is_string($callback) && false !== strpos($callback, '::')) {
            list($className, $methodName) = explode('::', $callback);

            return new ReflectionMethod($className, $methodName);
        }

        return new ReflectionFunction($callback);
    }

    private $subject;
    private $reflector;
    private $callFactory;
    private $calls;
}
