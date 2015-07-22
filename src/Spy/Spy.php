<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy;

use ArrayIterator;
use Eloquent\Phony\Call\Argument\Arguments;
use Eloquent\Phony\Call\Argument\ArgumentsInterface;
use Eloquent\Phony\Call\Argument\Exception\UndefinedArgumentException;
use Eloquent\Phony\Call\Call;
use Eloquent\Phony\Call\CallInterface;
use Eloquent\Phony\Call\Exception\UndefinedCallException;
use Eloquent\Phony\Call\Factory\CallFactory;
use Eloquent\Phony\Call\Factory\CallFactoryInterface;
use Eloquent\Phony\Collection\Exception\UndefinedIndexException;
use Eloquent\Phony\Collection\IndexNormalizer;
use Eloquent\Phony\Collection\IndexNormalizerInterface;
use Eloquent\Phony\Event\EventInterface;
use Eloquent\Phony\Event\Exception\UndefinedEventException;
use Eloquent\Phony\Invocation\AbstractWrappedInvocable;
use Eloquent\Phony\Spy\Factory\GeneratorSpyFactory;
use Eloquent\Phony\Spy\Factory\TraversableSpyFactory;
use Eloquent\Phony\Spy\Factory\TraversableSpyFactoryInterface;
use Exception;
use Iterator;

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
     * @param callable|null                       $callback              The callback, or null to create an unbound spy.
     * @param string|null                         $label                 The label.
     * @param boolean|null                        $useGeneratorSpies     True if generator spies should be used.
     * @param boolean|null                        $useTraversableSpies   True if traversable spies should be used.
     * @param IndexNormalizerInterface|null       $indexNormalizer       The index normalizer to use.
     * @param CallFactoryInterface|null           $callFactory           The call factory to use.
     * @param TraversableSpyFactoryInterface|null $generatorSpyFactory   The generator spy factory to use.
     * @param TraversableSpyFactoryInterface|null $traversableSpyFactory The traversable spy factory to use.
     */
    public function __construct(
        $callback = null,
        $label = null,
        $useGeneratorSpies = null,
        $useTraversableSpies = null,
        IndexNormalizerInterface $indexNormalizer = null,
        CallFactoryInterface $callFactory = null,
        TraversableSpyFactoryInterface $generatorSpyFactory = null,
        TraversableSpyFactoryInterface $traversableSpyFactory = null
    ) {
        if (null === $useGeneratorSpies) {
            $useGeneratorSpies = true;
        }
        if (null === $useTraversableSpies) {
            $useTraversableSpies = false;
        }
        if (null === $indexNormalizer) {
            $indexNormalizer = IndexNormalizer::instance();
        }
        if (null === $callFactory) {
            $callFactory = CallFactory::instance();
        }
        if (null === $generatorSpyFactory) {
            $generatorSpyFactory = GeneratorSpyFactory::instance();
        }
        if (null === $traversableSpyFactory) {
            $traversableSpyFactory = TraversableSpyFactory::instance();
        }

        parent::__construct($callback, $label);

        $this->useGeneratorSpies = $useGeneratorSpies;
        $this->useTraversableSpies = $useTraversableSpies;
        $this->indexNormalizer = $indexNormalizer;
        $this->callFactory = $callFactory;
        $this->generatorSpyFactory = $generatorSpyFactory;
        $this->traversableSpyFactory = $traversableSpyFactory;
        $this->calls = array();
    }

    /**
     * Turn on or off the use of generator spies.
     *
     * @param boolean $useGeneratorSpies True to use generator spies.
     */
    public function setUseGeneratorSpies($useGeneratorSpies)
    {
        $this->useGeneratorSpies = $useGeneratorSpies;
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
     * Turn on or off the use of traversable spies.
     *
     * @param boolean $useTraversableSpies True to use traversable spies.
     */
    public function setUseTraversableSpies($useTraversableSpies)
    {
        $this->useTraversableSpies = $useTraversableSpies;
    }

    /**
     * Returns true if this spy uses traversable spies.
     *
     * @return boolean True if this spy uses traversable spies.
     */
    public function useTraversableSpies()
    {
        return $this->useTraversableSpies;
    }

    /**
     * Get the index normalizer.
     *
     * @return IndexNormalizerInterface The index normalizer.
     */
    public function indexNormalizer()
    {
        return $this->indexNormalizer;
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
     * @return TraversableSpyFactoryInterface The generator spy factory.
     */
    public function generatorSpyFactory()
    {
        return $this->generatorSpyFactory;
    }

    /**
     * Get the traversable spy factory.
     *
     * @return TraversableSpyFactoryInterface The traversable spy factory.
     */
    public function traversableSpyFactory()
    {
        return $this->traversableSpyFactory;
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
     * Returns true if this collection contains any events.
     *
     * @return boolean True if this collection contains any events.
     */
    public function hasEvents()
    {
        return (boolean) $this->calls;
    }

    /**
     * Returns true if this collection contains any calls.
     *
     * @return boolean True if this collection contains any calls.
     */
    public function hasCalls()
    {
        return (boolean) $this->calls;
    }

    /**
     * Get the number of events.
     *
     * @return integer The event count.
     */
    public function eventCount()
    {
        return count($this->calls);
    }

    /**
     * Get the number of calls.
     *
     * @return integer The call count.
     */
    public function callCount()
    {
        return count($this->calls);
    }

    /**
     * Get the event count.
     *
     * @return integer The event count.
     */
    public function count()
    {
        return count($this->calls);
    }

    /**
     * Get all events as an array.
     *
     * @return array<EventInterface> The events.
     */
    public function allEvents()
    {
        return $this->calls;
    }

    /**
     * Get all calls as an array.
     *
     * @return array<CallInterface> The calls.
     */
    public function allCalls()
    {
        return $this->calls;
    }

    /**
     * Get an event by index.
     *
     * @param integer|null $index The index, or null for the first event.
     *
     * @return EventInterface          The event.
     * @throws UndefinedEventException If the requested event is undefined, or there are no events.
     */
    public function eventAt($index = null)
    {
        $count = count($this->calls);

        try {
            $normalized = $this->indexNormalizer->normalize($count, $index);
        } catch (UndefinedIndexException $e) {
            throw new UndefinedEventException($index, $e);
        }

        return $this->calls[$normalized];
    }

    /**
     * Get a call by index.
     *
     * @param integer|null $index The index, or null for the first call.
     *
     * @return CallInterface          The call.
     * @throws UndefinedCallException If the requested call is undefined, or there are no calls.
     */
    public function callAt($index = null)
    {
        $count = count($this->calls);

        try {
            $normalized = $this->indexNormalizer->normalize($count, $index);
        } catch (UndefinedIndexException $e) {
            throw new UndefinedCallException($index, $e);
        }

        return $this->calls[$normalized];
    }

    /**
     * Get the arguments.
     *
     * @return ArgumentsInterface|null The arguments.
     * @throws UndefinedCallException  If there are no calls.
     */
    public function arguments()
    {
        foreach ($this->calls as $call) {
            return $call->arguments();
        }

        throw new UndefinedCallException(0);
    }

    /**
     * Get an argument by index.
     *
     * @param integer|null $index The index, or null for the first argument.
     *
     * @return mixed                      The argument.
     * @throws UndefinedArgumentException If the requested argument is undefined, or no arguments were recorded.
     */
    public function argument($index = null)
    {
        foreach ($this->calls as $call) {
            return $call->arguments()->get($index);
        }

        throw new UndefinedArgumentException($index);
    }

    /**
     * Get an iterator for this collection.
     *
     * @return Iterator The iterator.
     */
    public function getIterator()
    {
        return new ArrayIterator($this->calls);
    }

    /**
     * Invoke this object.
     *
     * This method supports reference parameters.
     *
     * @param ArgumentsInterface|array|null The arguments.
     *
     * @return mixed     The result of invocation.
     * @throws Exception If an error occurs.
     */
    public function invokeWith($arguments = null)
    {
        $call = $this->callFactory
            ->record($this->callback, Arguments::adapt($arguments), $this);
        $exception = $call->exception();

        if ($exception) {
            throw $exception;
        }

        $returnValue = $call->returnValue();

        if (
            $this->useGeneratorSpies &&
            $this->generatorSpyFactory->isSupported($returnValue)
        ) {
            return $this->generatorSpyFactory->create($call, $returnValue);
        }

        if (
            $this->useTraversableSpies &&
            $this->traversableSpyFactory->isSupported($returnValue)
        ) {
            return $this->traversableSpyFactory->create($call, $returnValue);
        }

        return $returnValue;
    }

    private $useGeneratorSpies;
    private $useTraversableSpies;
    private $indexNormalizer;
    private $callFactory;
    private $generatorSpyFactory;
    private $traversableSpyFactory;
    private $calls;
}
