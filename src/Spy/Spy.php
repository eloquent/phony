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
     * @param callable|null             $subject     The subject, or null to create an unbound spy.
     * @param CallFactoryInterface|null $callFactory The call factory to use.
     */
    public function __construct(
        $subject = null,
        CallFactoryInterface $callFactory = null
    ) {
        if (null === $subject) {
            $subject = function () {};
        }
        if (null === $callFactory) {
            $callFactory = CallFactory::instance();
        }

        $this->subject = $subject;
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

    private $subject;
    private $callFactory;
    private $calls;
}
