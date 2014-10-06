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
use Eloquent\Phony\Invocation\AbstractWrappedInvocable;
use Eloquent\Phony\Spy\Factory\GeneratorSpyFactory;
use Eloquent\Phony\Spy\Factory\GeneratorSpyFactoryInterface;
use Exception;
use Generator;

/**
 * Spies on a function or method.
 *
 * @internal
 */
class Spy extends AbstractWrappedInvocable implements SpyInterface
{
    /**
     * Construct a new spy.
     *
     * @param callable|null                     $callback            The callback, or null to create an unbound spy.
     * @param boolean|null                      $useGeneratorSpies   True if generator spies should be used.
     * @param CallFactoryInterface|null         $callFactory         The call factory to use.
     * @param GeneratorSpyFactoryInterface|null $generatorSpyFactory The generator spy factory to use.
     */
    public function __construct(
        $callback = null,
        $useGeneratorSpies = null,
        CallFactoryInterface $callFactory = null,
        GeneratorSpyFactoryInterface $generatorSpyFactory = null
    ) {
        if (null === $useGeneratorSpies) {
            $useGeneratorSpies = !defined('HHVM_VERSION');
        }
        if (null === $callFactory) {
            $callFactory = CallFactory::instance();
        }
        if (null === $generatorSpyFactory) {
            $generatorSpyFactory = GeneratorSpyFactory::instance();
        }

        parent::__construct($callback);

        $this->useGeneratorSpies = $useGeneratorSpies;
        $this->callFactory = $callFactory;
        $this->generatorSpyFactory = $generatorSpyFactory;
        $this->calls = array();
    }

    /**
     * Returns true if this spy uses generator spies.
     *
     * @return boolean True if this spy uses generator spies.
     */
    public function useGeneratorSpies()
    {
        return $this->useGeneratorSpies;
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
     * Get the generator spy factory.
     *
     * @return GeneratorSpyFactoryInterface The generator spy factory.
     */
    public function generatorSpyFactory()
    {
        return $this->generatorSpyFactory;
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
     * Get the recorded calls.
     *
     * @return array<CallInterface> The recorded calls.
     */
    public function recordedCalls()
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
        $call = $this->callFactory->record(
            $this->callback,
            $arguments,
            $this,
            $this->useGeneratorSpies
        );
        $exception = $call->exception();

        if ($exception) {
            throw $exception;
        }

        $returnValue = $call->returnValue();

        if ($this->useGeneratorSpies && $returnValue instanceof Generator) {
            return $this->generatorSpyFactory->create($call, $returnValue);
        }

        return $returnValue;
    }

    private $useGeneratorSpies;
    private $callFactory;
    private $generatorSpyFactory;
    private $calls;
}
