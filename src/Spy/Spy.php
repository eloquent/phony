<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
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
use Eloquent\Phony\Call\Event\ThrewEventInterface;
use Eloquent\Phony\Call\Exception\UndefinedCallException;
use Eloquent\Phony\Call\Factory\CallFactory;
use Eloquent\Phony\Call\Factory\CallFactoryInterface;
use Eloquent\Phony\Collection\Exception\UndefinedIndexException;
use Eloquent\Phony\Collection\IndexNormalizer;
use Eloquent\Phony\Collection\IndexNormalizerInterface;
use Eloquent\Phony\Event\EventInterface;
use Eloquent\Phony\Event\Exception\UndefinedEventException;
use Eloquent\Phony\Invocation\AbstractWrappedInvocable;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Invocation\InvokerInterface;
use Eloquent\Phony\Spy\Factory\GeneratorSpyFactory;
use Eloquent\Phony\Spy\Factory\TraversableSpyFactory;
use Eloquent\Phony\Spy\Factory\TraversableSpyFactoryInterface;
use Error;
use Exception;
use Generator;
use Iterator;
use Traversable;

/**
 * Spies on a function or method.
 */
class Spy extends AbstractWrappedInvocable implements SpyInterface
{
    /**
     * Construct a new spy.
     *
     * @param callable|null                       $callback              The callback, or null to create an anonymous spy.
     * @param string|null                         $label                 The label.
     * @param IndexNormalizerInterface|null       $indexNormalizer       The index normalizer to use.
     * @param CallFactoryInterface|null           $callFactory           The call factory to use.
     * @param InvokerInterface|null               $invoker               The invoker to use.
     * @param TraversableSpyFactoryInterface|null $generatorSpyFactory   The generator spy factory to use.
     * @param TraversableSpyFactoryInterface|null $traversableSpyFactory The traversable spy factory to use.
     */
    public function __construct(
        $callback = null,
        $label = null,
        IndexNormalizerInterface $indexNormalizer = null,
        CallFactoryInterface $callFactory = null,
        InvokerInterface $invoker = null,
        TraversableSpyFactoryInterface $generatorSpyFactory = null,
        TraversableSpyFactoryInterface $traversableSpyFactory = null
    ) {
        if (!$indexNormalizer) {
            $indexNormalizer = IndexNormalizer::instance();
        }
        if (!$callFactory) {
            $callFactory = CallFactory::instance();
        }
        if (!$invoker) {
            $invoker = Invoker::instance();
        }
        if (!$generatorSpyFactory) {
            $generatorSpyFactory = GeneratorSpyFactory::instance();
        }
        if (!$traversableSpyFactory) {
            $traversableSpyFactory = TraversableSpyFactory::instance();
        }

        parent::__construct($callback, $label);

        $this->indexNormalizer = $indexNormalizer;
        $this->callFactory = $callFactory;
        $this->invoker = $invoker;
        $this->generatorSpyFactory = $generatorSpyFactory;
        $this->traversableSpyFactory = $traversableSpyFactory;

        $this->calls = array();
        $this->useGeneratorSpies = true;
        $this->useTraversableSpies = false;
        $this->isRecording = true;
    }

    /**
     * Turn on or off the use of generator spies.
     *
     * @param boolean $useGeneratorSpies True to use generator spies.
     *
     * @return $this This spy.
     */
    public function setUseGeneratorSpies($useGeneratorSpies)
    {
        $this->useGeneratorSpies = $useGeneratorSpies;

        return $this;
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
     *
     * @return $this This spy.
     */
    public function setUseTraversableSpies($useTraversableSpies)
    {
        $this->useTraversableSpies = $useTraversableSpies;

        return $this;
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
     * Get the invoker.
     *
     * @return InvokerInterface The invoker.
     */
    public function invoker()
    {
        return $this->invoker;
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
     * Stop recording calls.
     *
     * @return $this This spy.
     */
    public function stopRecording()
    {
        $this->isRecording = false;

        return $this;
    }

    /**
     * Start recording calls.
     *
     * @return $this This spy.
     */
    public function startRecording()
    {
        $this->isRecording = true;

        return $this;
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
     * Get the first event.
     *
     * @return EventInterface          The event.
     * @throws UndefinedEventException If there are no events.
     */
    public function firstEvent()
    {
        if (!$this->calls) {
            throw new UndefinedEventException(0);
        }

        return $this->calls[0];
    }

    /**
     * Get the last event.
     *
     * @return EventInterface          The event.
     * @throws UndefinedEventException If there are no events.
     */
    public function lastEvent()
    {
        if ($count = count($this->calls)) {
            return $this->calls[$count - 1];
        }

        throw new UndefinedEventException(0);
    }

    /**
     * Get an event by index.
     *
     * Negative indices are offset from the end of the list. That is, `-1`
     * indicates the last element, and `-2` indicates the second last element.
     *
     * @param integer $index The index.
     *
     * @return EventInterface          The event.
     * @throws UndefinedEventException If the requested event is undefined, or there are no events.
     */
    public function eventAt($index = 0)
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
     * Get the first call.
     *
     * @return CallInterface          The call.
     * @throws UndefinedCallException If there are no calls.
     */
    public function firstCall()
    {
        if (isset($this->calls[0])) {
            return $this->calls[0];
        }

        throw new UndefinedCallException(0);
    }

    /**
     * Get the last call.
     *
     * @return CallInterface          The call.
     * @throws UndefinedCallException If there are no calls.
     */
    public function lastCall()
    {
        if ($count = count($this->calls)) {
            return $this->calls[$count - 1];
        }

        throw new UndefinedCallException(0);
    }

    /**
     * Get a call by index.
     *
     * Negative indices are offset from the end of the list. That is, `-1`
     * indicates the last element, and `-2` indicates the second last element.
     *
     * @param integer $index The index.
     *
     * @return CallInterface          The call.
     * @throws UndefinedCallException If the requested call is undefined, or there are no calls.
     */
    public function callAt($index = 0)
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
     * Negative indices are offset from the end of the list. That is, `-1`
     * indicates the last element, and `-2` indicates the second last element.
     *
     * @param integer $index The index.
     *
     * @return mixed                      The argument.
     * @throws UndefinedArgumentException If the requested argument is undefined, or no arguments were recorded.
     */
    public function argument($index = 0)
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
     * @param ArgumentsInterface|array $arguments The arguments.
     *
     * @return mixed           The result of invocation.
     * @throws Exception|Error If an error occurs.
     */
    public function invokeWith($arguments = array())
    {
        if (!$arguments instanceof ArgumentsInterface) {
            $arguments = new Arguments($arguments);
        }

        if (!$this->isRecording) {
            return $this->invoker->callWith($this->callback, $arguments);
        }

        $call = $this->callFactory->record($this->callback, $arguments, $this);
        $responseEvent = $call->responseEvent();

        if ($responseEvent instanceof ThrewEventInterface) {
            $call->setEndEvent($responseEvent);

            throw $responseEvent->exception();
        }

        $returnValue = $responseEvent->value();

        if ($this->useGeneratorSpies && $returnValue instanceof Generator) {
            return $this->generatorSpyFactory->create($call, $returnValue);
        }

        if (
            $this->useTraversableSpies &&
            ($returnValue instanceof Traversable || is_array($returnValue))
        ) {
            return $this->traversableSpyFactory->create($call, $returnValue);
        }

        $call->setEndEvent($call->responseEvent());

        return $returnValue;
    }

    private $indexNormalizer;
    private $callFactory;
    private $invoker;
    private $generatorSpyFactory;
    private $traversableSpyFactory;
    private $useGeneratorSpies;
    private $useTraversableSpies;
    private $isRecording;
    private $calls;
}
