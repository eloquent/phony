<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call;

use Eloquent\Phony\Assertion\Recorder\AssertionRecorder;
use Eloquent\Phony\Assertion\Recorder\AssertionRecorderInterface;
use Eloquent\Phony\Assertion\Renderer\AssertionRenderer;
use Eloquent\Phony\Assertion\Renderer\AssertionRendererInterface;
use Eloquent\Phony\Call\Argument\Exception\UndefinedArgumentException;
use Eloquent\Phony\Call\Event\CalledEventInterface;
use Eloquent\Phony\Call\Event\EndEventInterface;
use Eloquent\Phony\Call\Event\ProducedEventInterface;
use Eloquent\Phony\Call\Event\ReceivedEventInterface;
use Eloquent\Phony\Call\Event\ReceivedExceptionEventInterface;
use Eloquent\Phony\Call\Event\ResponseEventInterface;
use Eloquent\Phony\Call\Event\TraversableEventInterface;
use Eloquent\Phony\Call\Exception\UndefinedCallException;
use Eloquent\Phony\Call\Exception\UndefinedResponseException;
use Eloquent\Phony\Cardinality\Verification\AbstractCardinalityVerifier;
use Eloquent\Phony\Event\EventCollectionInterface;
use Eloquent\Phony\Event\EventInterface;
use Eloquent\Phony\Event\Exception\UndefinedEventException;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\InvocableInspectorInterface;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Factory\MatcherFactoryInterface;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Matcher\Verification\MatcherVerifierInterface;
use Error;
use Exception;
use InvalidArgumentException;
use Iterator;
use Throwable;

/**
 * Provides convenience methods for verifying the details of a call.
 */
class CallVerifier extends AbstractCardinalityVerifier implements
    CallVerifierInterface
{
    /**
     * Construct a new call verifier.
     *
     * @param CallInterface                    $call               The call.
     * @param MatcherFactoryInterface|null     $matcherFactory     The matcher factory to use.
     * @param MatcherVerifierInterface|null    $matcherVerifier    The matcher verifier to use.
     * @param AssertionRecorderInterface|null  $assertionRecorder  The assertion recorder to use.
     * @param AssertionRendererInterface|null  $assertionRenderer  The assertion renderer to use.
     * @param InvocableInspectorInterface|null $invocableInspector The invocable inspector to use.
     */
    public function __construct(
        CallInterface $call,
        MatcherFactoryInterface $matcherFactory = null,
        MatcherVerifierInterface $matcherVerifier = null,
        AssertionRecorderInterface $assertionRecorder = null,
        AssertionRendererInterface $assertionRenderer = null,
        InvocableInspectorInterface $invocableInspector = null
    ) {
        if (null === $matcherFactory) {
            $matcherFactory = MatcherFactory::instance();
        }
        if (null === $matcherVerifier) {
            $matcherVerifier = MatcherVerifier::instance();
        }
        if (null === $assertionRecorder) {
            $assertionRecorder = AssertionRecorder::instance();
        }
        if (null === $assertionRenderer) {
            $assertionRenderer = AssertionRenderer::instance();
        }
        if (null === $invocableInspector) {
            $invocableInspector = InvocableInspector::instance();
        }

        parent::__construct();

        $this->call = $call;
        $this->matcherFactory = $matcherFactory;
        $this->matcherVerifier = $matcherVerifier;
        $this->assertionRecorder = $assertionRecorder;
        $this->assertionRenderer = $assertionRenderer;
        $this->invocableInspector = $invocableInspector;

        $this->argumentCount = count($call->arguments());
    }

    /**
     * Get the call.
     *
     * @return CallInterface The call.
     */
    public function call()
    {
        return $this->call;
    }

    /**
     * Get the matcher factory.
     *
     * @return MatcherFactoryInterface The matcher factory.
     */
    public function matcherFactory()
    {
        return $this->matcherFactory;
    }

    /**
     * Get the matcher verifier.
     *
     * @return MatcherVerifierInterface The matcher verifier.
     */
    public function matcherVerifier()
    {
        return $this->matcherVerifier;
    }

    /**
     * Get the assertion recorder.
     *
     * @return AssertionRecorderInterface The assertion recorder.
     */
    public function assertionRecorder()
    {
        return $this->assertionRecorder;
    }

    /**
     * Get the assertion renderer.
     *
     * @return AssertionRendererInterface The assertion renderer.
     */
    public function assertionRenderer()
    {
        return $this->assertionRenderer;
    }

    /**
     * Get the invocable inspector.
     *
     * @return InvocableInspectorInterface The invocable inspector.
     */
    public function invocableInspector()
    {
        return $this->invocableInspector;
    }

    /**
     * Get the sequence number.
     *
     * The sequence number is a unique number assigned to every event that Phony
     * records. The numbers are assigned sequentially, meaning that sequence
     * numbers can be used to determine event order.
     *
     * @return integer The sequence number.
     */
    public function sequenceNumber()
    {
        return $this->call->sequenceNumber();
    }

    /**
     * Get the time at which the event occurred.
     *
     * @return float The time at which the event occurred, in seconds since the Unix epoch.
     */
    public function time()
    {
        return $this->call->time();
    }

    /**
     * Returns true if this collection contains any events.
     *
     * @return boolean True if this collection contains any events.
     */
    public function hasEvents()
    {
        return $this->call->hasEvents();
    }

    /**
     * Returns true if this collection contains any calls.
     *
     * @return boolean True if this collection contains any calls.
     */
    public function hasCalls()
    {
        return $this->call->hasCalls();
    }

    /**
     * Get the number of events.
     *
     * @return integer The event count.
     */
    public function eventCount()
    {
        return $this->call->eventCount();
    }

    /**
     * Get the number of calls.
     *
     * @return integer The call count.
     */
    public function callCount()
    {
        return $this->call->callCount();
    }

    /**
     * Get the event count.
     *
     * @return integer The event count.
     */
    public function count()
    {
        return $this->call->count();
    }

    /**
     * Get the first event.
     *
     * @return EventInterface          The event.
     * @throws UndefinedEventException If there are no events.
     */
    public function firstEvent()
    {
        return $this->call->firstEvent();
    }

    /**
     * Get the last event.
     *
     * @return EventInterface          The event.
     * @throws UndefinedEventException If there are no events.
     */
    public function lastEvent()
    {
        return $this->call->lastEvent();
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
        return $this->call->eventAt($index);
    }

    /**
     * Get the first call.
     *
     * @return CallInterface          The call.
     * @throws UndefinedCallException If there are no calls.
     */
    public function firstCall()
    {
        return $this->call->firstCall();
    }

    /**
     * Get the last call.
     *
     * @return CallInterface          The call.
     * @throws UndefinedCallException If there are no calls.
     */
    public function lastCall()
    {
        return $this->call->lastCall();
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
        return $this->call->callAt($index);
    }

    /**
     * Get an iterator for this collection.
     *
     * @return Iterator The iterator.
     */
    public function getIterator()
    {
        return $this->call->getIterator();
    }

    /**
     * Get the 'called' event.
     *
     * @return CalledEventInterface The 'called' event.
     */
    public function calledEvent()
    {
        return $this->call->calledEvent();
    }

    /**
     * Set the response event.
     *
     * @param ResponseEventInterface $responseEvent The response event.
     *
     * @throws InvalidArgumentException If the call has already responded.
     */
    public function setResponseEvent(ResponseEventInterface $responseEvent)
    {
        $this->call->setResponseEvent($responseEvent);
    }

    /**
     * Get the response event.
     *
     * @return ResponseEventInterface|null The response event, or null if the call has not yet responded.
     */
    public function responseEvent()
    {
        return $this->call->responseEvent();
    }

    /**
     * Add a traversable event.
     *
     * @param TraversableEventInterface $traversableEvent The traversable event.
     *
     * @throws InvalidArgumentException If the call has already completed.
     */
    public function addTraversableEvent(
        TraversableEventInterface $traversableEvent
    ) {
        $this->call->addTraversableEvent($traversableEvent);
    }

    /**
     * Get the traversable events.
     *
     * @return array<TraversableEventInterface> The traversable events.
     */
    public function traversableEvents()
    {
        return $this->call->traversableEvents();
    }

    /**
     * Set the end event.
     *
     * @param EndEventInterface $endEvent The end event.
     *
     * @throws InvalidArgumentException If the call has already completed.
     */
    public function setEndEvent(EndEventInterface $endEvent)
    {
        $this->call->setEndEvent($endEvent);
    }

    /**
     * Get the end event.
     *
     * @return EndEventInterface|null The end event, or null if the call has not yet completed.
     */
    public function endEvent()
    {
        return $this->call->endEvent();
    }

    /**
     * Get all events as an array.
     *
     * @return array<EventInterface> The events.
     */
    public function allEvents()
    {
        return $this->call->allEvents();
    }

    /**
     * Get all calls as an array.
     *
     * @return array<CallInterface> The calls.
     */
    public function allCalls()
    {
        return $this->call->allCalls();
    }

    /**
     * Returns true if this call has responded.
     *
     * A call that has responded has returned a value, or thrown an exception.
     *
     * @return boolean True if this call has responded.
     */
    public function hasResponded()
    {
        return $this->call->hasResponded();
    }

    /**
     * Returns true if this call has responded with a traversable.
     *
     * @return boolean True if this call has responded with a traversable.
     */
    public function isTraversable()
    {
        return $this->call->isTraversable();
    }

    /**
     * Returns true if this call has responded with a generator.
     *
     * @return boolean True if this call has responded with a generator.
     */
    public function isGenerator()
    {
        return $this->call->isGenerator();
    }

    /**
     * Returns true if this call has completed.
     *
     * When generator spies are in use, a call that returns a generator will not
     * be considered complete until the generator has been completely consumed
     * via iteration.
     *
     * Similarly, when traversable spies are in use, a call that returns a
     * traversable will not be considered complete until the traversable has
     * been completely consumed via iteration.
     *
     * @return boolean True if this call has completed.
     */
    public function hasCompleted()
    {
        return $this->call->hasCompleted();
    }

    /**
     * Get the callback.
     *
     * @return callable The callback.
     */
    public function callback()
    {
        return $this->call->callback();
    }

    /**
     * Get the received arguments.
     *
     * @return ArgumentsInterface The received arguments.
     */
    public function arguments()
    {
        return $this->call->arguments();
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
        return $this->call->argument($index);
    }

    /**
     * Get the returned value.
     *
     * @return mixed                      The returned value.
     * @throws UndefinedResponseException If this call has not yet returned a value.
     */
    public function returnValue()
    {
        return $this->call->returnValue();
    }

    /**
     * Get the thrown exception.
     *
     * @return Exception|Error            The thrown exception.
     * @throws UndefinedResponseException If this call has not yet thrown an exception.
     */
    public function exception()
    {
        return $this->call->exception();
    }

    /**
     * Get the response.
     *
     * @return tuple<Exception|Error|null,mixed> A 2-tuple of thrown exception or null, and return value.
     * @throws UndefinedResponseException        If this call has not yet responded.
     */
    public function response()
    {
        return $this->call->response();
    }

    /**
     * Get the time at which the call responded.
     *
     * A call that has responded has returned a value, or thrown an exception.
     *
     * @return float|null The time at which the call responded, in seconds since the Unix epoch, or null if the call has not yet responded.
     */
    public function responseTime()
    {
        return $this->call->responseTime();
    }

    /**
     * Get the time at which the call completed.
     *
     * When generator spies are in use, a call that returns a generator will not
     * be considered complete until the generator has been completely consumed
     * via iteration.
     *
     * Similarly, when traversable spies are in use, a call that returns a
     * traversable will not be considered complete until the traversable has
     * been completely consumed via iteration.
     *
     * @return float|null The time at which the call completed, in seconds since the Unix epoch, or null if the call has not yet completed.
     */
    public function endTime()
    {
        return $this->call->endTime();
    }

    /**
     * Get the call duration.
     *
     * @return float|null The call duration in seconds, or null if the call has not yet completed.
     */
    public function duration()
    {
        $endTime = $this->call->endTime();

        if (null === $endTime) {
            return;
        }

        return $endTime - $this->call->time();
    }

    /**
     * Get the call response duration.
     *
     * @return float|null The call response duration in seconds, or null if the call has not yet responded.
     */
    public function responseDuration()
    {
        $responseTime = $this->call->responseTime();

        if (null === $responseTime) {
            return;
        }

        return $responseTime - $this->call->time();
    }

    /**
     * Get the number of arguments.
     *
     * @return integer The number of arguments.
     */
    public function argumentCount()
    {
        return $this->argumentCount;
    }

    /**
     * Checks if called with the supplied arguments.
     *
     * @param mixed ...$argument The arguments.
     *
     * @return EventCollectionInterface|null        The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     */
    public function checkCalledWith()
    {
        $cardinality = $this->resetCardinality()->assertSingular();

        $matchers = $this->matcherFactory->adaptAll(func_get_args());

        list($matchCount, $matchingEvents) = $this->matchIf(
            $this->call,
            $this->matcherVerifier->matches($matchers, $this->call->arguments())
        );

        if ($cardinality->matches($matchCount, 1)) {
            return $this->assertionRecorder->createSuccess($matchingEvents);
        }
    }

    /**
     * Throws an exception unless called with the supplied arguments.
     *
     * @param mixed ...$argument The arguments.
     *
     * @return EventCollectionInterface             The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     * @throws Exception                            If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function calledWith()
    {
        $cardinality = $this->cardinality;

        $matchers = $this->matcherFactory->adaptAll(func_get_args());

        if (
            $result =
                call_user_func_array(array($this, 'checkCalledWith'), $matchers)
        ) {
            return $result;
        }

        return $this->assertionRecorder->createFailure(
            sprintf(
                "Expected %s with arguments like:\n    %s\nArguments:\n    %s",
                $this->assertionRenderer
                    ->renderCardinality($cardinality, 'call'),
                $this->assertionRenderer->renderMatchers($matchers),
                $this->assertionRenderer
                    ->renderArguments($this->call->arguments())
            )
        );
    }

    /**
     * Checks if the $this value is equal to the supplied value.
     *
     * @param object|null $value The possible $this value.
     *
     * @return EventCollectionInterface|null        The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     */
    public function checkCalledOn($value)
    {
        $cardinality = $this->resetCardinality()->assertSingular();

        $thisValue = $this->invocableInspector
            ->callbackThisValue($this->call->callback());

        if ($this->matcherFactory->isMatcher($value)) {
            $value = $this->matcherFactory->adapt($value);

            list($matchCount, $matchingEvents) =
                $this->matchIf($this->call, $value->matches($thisValue));

            if ($cardinality->matches($matchCount, 1)) {
                return $this->assertionRecorder->createSuccess($matchingEvents);
            }

            return;
        }

        list($matchCount, $matchingEvents) =
            $this->matchIf($this->call, $thisValue === $value);

        if ($cardinality->matches($matchCount, 1)) {
            return $this->assertionRecorder->createSuccess($matchingEvents);
        }
    }

    /**
     * Throws an exception unless the $this value is equal to the supplied
     * value.
     *
     * @param object|null $value The possible $this value.
     *
     * @return EventCollectionInterface             The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     * @throws Exception                            If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function calledOn($value)
    {
        $cardinality = $this->cardinality;

        if ($this->matcherFactory->isMatcher($value)) {
            $isMatcher = true;
            $value = $this->matcherFactory->adapt($value);
        } else {
            $isMatcher = false;
        }

        if ($result = $this->checkCalledOn($value)) {
            return $result;
        }

        $renderedThisValue = $this->assertionRenderer->renderValue(
            $this->invocableInspector
                ->callbackThisValue($this->call->callback())
        );

        if ($isMatcher) {
            if ($cardinality->isNever()) {
                $message = 'Called on object like %s. Object was %s.';
            } else {
                $message = 'Not called on object like %s. Object was %s.';
            }

            return $this->assertionRecorder->createFailure(
                sprintf($message, $value->describe(), $renderedThisValue)
            );
        }

        if ($cardinality->isNever()) {
            $message = 'Called on supplied object. Object was %s.';
        } else {
            $message = 'Not called on supplied object. Object was %s.';
        }

        return $this->assertionRecorder
            ->createFailure(sprintf($message, $renderedThisValue));
    }

    /**
     * Checks if this call returned the supplied value.
     *
     * When called with no arguments, this method simply checks that the call
     * returned any value.
     *
     * @param mixed $value The value.
     *
     * @return EventCollectionInterface|null        The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     */
    public function checkReturned($value = null)
    {
        $cardinality = $this->resetCardinality()->assertSingular();

        if ($responseEvent = $this->call->responseEvent()) {
            list($exception, $returnValue) = $this->call->response();

            $hasReturned = !$exception;
        } else {
            $returnValue = null;
            $hasReturned = false;
        }

        if (0 === func_num_args()) {
            list($matchCount, $matchingEvents) =
                $this->matchIf($responseEvent, $hasReturned);

            if ($cardinality->matches($matchCount, 1)) {
                return $this->assertionRecorder->createSuccess($matchingEvents);
            }

            return;
        }

        $value = $this->matcherFactory->adapt($value);

        list($matchCount, $matchingEvents) = $this->matchIf(
            $responseEvent,
            $hasReturned && $value->matches($returnValue)
        );

        if ($cardinality->matches($matchCount, 1)) {
            return $this->assertionRecorder->createSuccess($matchingEvents);
        }
    }

    /**
     * Throws an exception unless this call returned the supplied value.
     *
     * When called with no arguments, this method simply checks that the call
     * returned any value.
     *
     * @param mixed $value The value.
     *
     * @return EventCollectionInterface             The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     * @throws Exception                            If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function returned($value = null)
    {
        $cardinality = $this->cardinality;

        $argumentCount = func_num_args();

        if (0 === $argumentCount) {
            $arguments = array();
        } else {
            $value = $this->matcherFactory->adapt($value);
            $arguments = array($value);
        }

        if (
            $result =
                call_user_func_array(array($this, 'checkReturned'), $arguments)
        ) {
            return $result;
        }

        if (0 === $argumentCount) {
            $renderedType = 'return';
        } else {
            $renderedType = sprintf('return like %s', $value->describe());
        }

        return $this->assertionRecorder->createFailure(
            sprintf(
                'Expected %s. %s',
                $this->assertionRenderer
                    ->renderCardinality($cardinality, $renderedType),
                $this->assertionRenderer->renderResponse($this->call)
            )
        );
    }

    /**
     * Checks if an exception of the supplied type was thrown.
     *
     * @param Exception|Error|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return EventCollectionInterface|null        The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     * @throws InvalidArgumentException             If the type is invalid.
     */
    public function checkThrew($type = null)
    {
        $cardinality = $this->resetCardinality()->assertSingular();

        if ($responseEvent = $this->call->responseEvent()) {
            list($exception) = $this->call->response();
        } else {
            $exception = null;
        }

        $isTypeSupported = false;

        if (null === $type) {
            $isTypeSupported = true;

            list($matchCount, $matchingEvents) =
                $this->matchIf($responseEvent, $exception);

            if ($cardinality->matches($matchCount, 1)) {
                return $this->assertionRecorder->createSuccess($matchingEvents);
            }
        } elseif (is_string($type)) {
            $isTypeSupported = true;

            list($matchCount, $matchingEvents) =
                $this->matchIf($responseEvent, is_a($exception, $type));

            if ($cardinality->matches($matchCount, 1)) {
                return $this->assertionRecorder->createSuccess($matchingEvents);
            }
        } elseif (is_object($type)) {
            if ($type instanceof Throwable || $type instanceof Exception) {
                $isTypeSupported = true;

                list($matchCount, $matchingEvents) =
                    $this->matchIf($responseEvent, $exception == $type);

                if (
                    $cardinality->matches($matchCount, 1)
                ) {
                    return $this->assertionRecorder
                        ->createSuccess($matchingEvents);
                }
            } elseif ($this->matcherFactory->isMatcher($type)) {
                $isTypeSupported = true;

                $type = $this->matcherFactory->adapt($type);
                list($matchCount, $matchingEvents) = $this->matchIf(
                    $responseEvent,
                    $exception && $type->matches($exception)
                );

                if (
                    $cardinality->matches($matchCount, 1)
                ) {
                    return $this->assertionRecorder
                        ->createSuccess($matchingEvents);
                }
            }
        }

        if (!$isTypeSupported) {
            throw new InvalidArgumentException(
                sprintf(
                    'Unable to match exceptions against %s.',
                    $this->assertionRenderer->renderValue($type)
                )
            );
        }
    }

    /**
     * Throws an exception unless this call threw an exception of the supplied
     * type.
     *
     * @param Exception|Error|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return EventCollectionInterface             The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     * @throws InvalidArgumentException             If the type is invalid.
     * @throws Exception                            If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function threw($type = null)
    {
        $cardinality = $this->cardinality;

        if ($result = $this->checkThrew($type)) {
            return $result;
        }

        if (null === $type) {
            $renderedType = 'exception';
        } elseif (is_string($type)) {
            $renderedType = sprintf('%s exception', $type);
        } elseif (is_object($type)) {
            if ($type instanceof Throwable || $type instanceof Exception) {
                $renderedType = sprintf(
                    'exception equal to %s',
                    $this->assertionRenderer->renderException($type)
                );
            } elseif ($this->matcherFactory->isMatcher($type)) {
                $renderedType = sprintf(
                    'exception like %s',
                    $this->matcherFactory->adapt($type)->describe()
                );
            }
        }

        return $this->assertionRecorder->createFailure(
            sprintf(
                'Expected %s. %s',
                $this->assertionRenderer
                    ->renderCardinality($cardinality, $renderedType),
                $this->assertionRenderer->renderResponse($this->call)
            )
        );
    }

    /**
     * Checks if this call produced the supplied values.
     *
     * When called with no arguments, this method simply checks that the call
     * produced any value.
     *
     * With a single argument, it checks that a value matching the argument was
     * produced.
     *
     * With two arguments, it checks that a key and value matching the
     * respective arguments were produced together.
     *
     * @param mixed $keyOrValue The key or value.
     * @param mixed $value      The value.
     *
     * @return EventCollectionInterface|null The result.
     */
    public function checkProduced($keyOrValue = null, $value = null)
    {
        $cardinality = $this->resetCardinality();

        $argumentCount = func_num_args();

        if (0 === $argumentCount) {
            $checkKey = false;
            $checkValue = false;
        } elseif (1 === $argumentCount) {
            $checkKey = false;
            $checkValue = true;
            $value = $this->matcherFactory->adapt($keyOrValue);
        } else {
            $checkKey = true;
            $checkValue = true;
            $key = $this->matcherFactory->adapt($keyOrValue);
            $value = $this->matcherFactory->adapt($value);
        }

        $matchingEvents = array();
        $matchCount = 0;
        $totalCount = 0;

        foreach ($this->call->traversableEvents() as $event) {
            if ($event instanceof ProducedEventInterface) {
                ++$totalCount;

                if ($checkKey && !$key->matches($event->key())) {
                    continue;
                }
                if ($checkValue && !$value->matches($event->value())) {
                    continue;
                }

                $matchingEvents[] = $event;
                ++$matchCount;
            }
        }

        if ($cardinality->matches($matchCount, $totalCount)) {
            return $this->assertionRecorder->createSuccess($matchingEvents);
        }
    }

    /**
     * Throws an exception unless this call produced the supplied values.
     *
     * When called with no arguments, this method simply checks that the call
     * produced any value.
     *
     * With a single argument, it checks that a value matching the argument was
     * produced.
     *
     * With two arguments, it checks that a key and value matching the
     * respective arguments were produced together.
     *
     * @param mixed $keyOrValue The key or value.
     * @param mixed $value      The value.
     *
     * @return EventCollectionInterface The result.
     * @throws Exception                If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function produced($keyOrValue = null, $value = null)
    {
        $cardinality = $this->cardinality;

        $argumentCount = func_num_args();

        if (0 === $argumentCount) {
            $arguments = array();
        } elseif (1 === $argumentCount) {
            $value = $this->matcherFactory->adapt($keyOrValue);
            $arguments = array($value);
        } else {
            $key = $this->matcherFactory->adapt($keyOrValue);
            $value = $this->matcherFactory->adapt($value);
            $arguments = array($key, $value);
        }

        if (
            $result =
                call_user_func_array(array($this, 'checkProduced'), $arguments)
        ) {
            return $result;
        }

        if (0 === $argumentCount) {
            $renderedType = 'call to produce';
        } elseif (1 === $argumentCount) {
            $renderedType =
                sprintf('call to produce like %s', $value->describe());
        } else {
            $renderedType = sprintf(
                'call to produce like %s: %s',
                $key->describe(),
                $value->describe()
            );
        }

        if ($this->call->traversableEvents()) {
            $renderedProduced = sprintf(
                ":\n%s",
                $this->assertionRenderer->renderProduced($this->call)
            );
        } else {
            $renderedProduced = ' nothing.';
        }

        return $this->assertionRecorder->createFailure(
            sprintf(
                'Expected %s. Produced%s',
                $this->assertionRenderer
                    ->renderCardinality($cardinality, $renderedType),
                $renderedProduced
            )
        );
    }

    /**
     * Checks if this call produced all of the supplied key-value pairs, in the
     * supplied order.
     *
     * @param mixed ...$pairs The key-value pairs.
     *
     * @return EventCollectionInterface|null The result.
     */
    public function checkProducedAll()
    {
        $cardinality = $this->resetCardinality()->assertSingular();

        $pairCount = func_num_args();
        $producedEvents = array();
        $lastEvent = $this->call->responseEvent();

        foreach ($this->call->traversableEvents() as $event) {
            if ($event instanceof ProducedEventInterface) {
                $producedEvents[] = $event;
                $lastEvent = $event;
            }
        }

        if (count($producedEvents) === $pairCount) {
            $isMatch = true;

            foreach (func_get_args() as $index => $pair) {
                if (is_array($pair)) {
                    $checkKey = true;
                    $key = $this->matcherFactory->adapt($pair[0]);
                    $value = $this->matcherFactory->adapt($pair[1]);
                } else {
                    $checkKey = false;
                    $value = $this->matcherFactory->adapt($pair);
                }

                if (!$value->matches($producedEvents[$index]->value())) {
                    $isMatch = false;

                    break;
                }

                if (
                    $checkKey &&
                    !$key->matches($producedEvents[$index]->key())
                ) {
                    $isMatch = false;

                    break;
                }
            }
        } else {
            $isMatch = false;
        }

        list($matchCount, $matchingEvents) =
            $this->matchIf($lastEvent, $isMatch);

        if ($cardinality->matches($matchCount, 1)) {
            return $this->assertionRecorder->createSuccess($matchingEvents);
        }
    }

    /**
     * Throws an exception unless this call produced all of the supplied
     * key-value pairs, in the supplied order.
     *
     * @param mixed ...$pairs The key-value pairs.
     *
     * @return EventCollectionInterface The result.
     * @throws Exception                If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function producedAll()
    {
        $cardinality = $this->cardinality;

        $pairs = array();

        foreach (func_get_args() as $pair) {
            if (is_array($pair)) {
                $pairs[] = array(
                    $this->matcherFactory->adapt($pair[0]),
                    $this->matcherFactory->adapt($pair[1]),
                );
            } else {
                $pairs[] = $this->matcherFactory->adapt($pair);
            }
        }

        if (
            $result =
                call_user_func_array(array($this, 'checkProducedAll'), $pairs)
        ) {
            return $result;
        }

        if (0 === func_num_args()) {
            $renderedType = 'call to produce nothing. ';
        } else {
            $renderedType = 'call to produce like:';

            foreach ($pairs as $pair) {
                if (is_array($pair)) {
                    $renderedType .= sprintf(
                        "\n    - %s: %s",
                        $pair[0]->describe(),
                        $pair[1]->describe()
                    );
                } else {
                    $renderedType .= sprintf("\n    - %s", $pair->describe());
                }
            }

            $renderedType .= "\n";
        }

        if ($this->call->traversableEvents()) {
            $renderedProduced = sprintf(
                ":\n%s",
                $this->assertionRenderer->renderProduced($this->call)
            );
        } else {
            $renderedProduced = ' nothing.';
        }

        return $this->assertionRecorder->createFailure(
            sprintf(
                'Expected %sProduced%s',
                $this->assertionRenderer
                    ->renderCardinality($cardinality, $renderedType),
                $renderedProduced
            )
        );
    }

    /**
     * Checks if this call received the supplied value.
     *
     * When called with no arguments, this method simply checks that the call
     * received any value.
     *
     * @param mixed $value The value.
     *
     * @return EventCollectionInterface|null The result.
     */
    public function checkReceived($value = null)
    {
        $cardinality = $this->resetCardinality();

        $argumentCount = func_num_args();

        if (0 === $argumentCount) {
            $checkValue = false;
        } else {
            $checkValue = true;
            $value = $this->matcherFactory->adapt($value);
        }

        $matchingEvents = array();
        $matchCount = 0;
        $totalCount = 0;

        foreach ($this->call->traversableEvents() as $event) {
            if ($event instanceof ReceivedEventInterface) {
                ++$totalCount;

                if (!$checkValue || $value->matches($event->value())) {
                    $matchingEvents[] = $event;
                    ++$matchCount;
                }
            } elseif ($event instanceof ReceivedExceptionEventInterface) {
                ++$totalCount;
            }
        }

        if ($cardinality->matches($matchCount, $totalCount)) {
            return $this->assertionRecorder->createSuccess($matchingEvents);
        }
    }

    /**
     * Throws an exception unless this call received the supplied value.
     *
     * When called with no arguments, this method simply checks that the call
     * received any value.
     *
     * @param mixed $value The value.
     *
     * @return EventCollectionInterface The result.
     * @throws Exception                If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function received($value = null)
    {
        $cardinality = $this->cardinality;

        $argumentCount = func_num_args();

        if (0 === $argumentCount) {
            $arguments = array();
        } else {
            $value = $this->matcherFactory->adapt($value);
            $arguments = array($value);
        }

        if (
            $result =
                call_user_func_array(array($this, 'checkReceived'), $arguments)
        ) {
            return $result;
        }

        if (0 === $argumentCount) {
            $renderedType = 'generator to receive value';
        } else {
            $renderedType = sprintf(
                'generator to receive value like %s',
                $value->describe()
            );
        }

        if ($this->call->traversableEvents()) {
            $renderedProduced = sprintf(
                ":\n%s",
                $this->assertionRenderer->renderProduced($this->call)
            );
        } else {
            $renderedProduced = ' nothing.';
        }

        return $this->assertionRecorder->createFailure(
            sprintf(
                'Expected %s. Produced%s',
                $this->assertionRenderer
                    ->renderCardinality($cardinality, $renderedType),
                $renderedProduced
            )
        );
    }

    /**
     * Checks if this call received an exception of the supplied type.
     *
     * @param Exception|Error|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return EventCollectionInterface|null The result.
     */
    public function checkReceivedException($type = null)
    {
        $cardinality = $this->resetCardinality();

        $traversableEvents = $this->call->traversableEvents();
        $matchingEvents = array();
        $matchCount = 0;
        $totalCount = 0;
        $isTypeSupported = false;

        if (null === $type) {
            $isTypeSupported = true;

            foreach ($traversableEvents as $event) {
                if ($event instanceof ReceivedExceptionEventInterface) {
                    ++$totalCount;
                    $matchingEvents[] = $event;
                    ++$matchCount;
                } elseif ($event instanceof ReceivedEventInterface) {
                    ++$totalCount;
                }
            }
        } elseif (is_string($type)) {
            $isTypeSupported = true;

            foreach ($traversableEvents as $event) {
                if ($event instanceof ReceivedExceptionEventInterface) {
                    ++$totalCount;

                    if (is_a($event->exception(), $type)) {
                        $matchingEvents[] = $event;
                        ++$matchCount;
                    }
                } elseif ($event instanceof ReceivedEventInterface) {
                    ++$totalCount;
                }
            }
        } elseif (is_object($type)) {
            if ($type instanceof Throwable || $type instanceof Exception) {
                $isTypeSupported = true;

                foreach ($traversableEvents as $event) {
                    if ($event instanceof ReceivedExceptionEventInterface) {
                        ++$totalCount;

                        if ($event->exception() == $type) {
                            $matchingEvents[] = $event;
                            ++$matchCount;
                        }
                    } elseif ($event instanceof ReceivedEventInterface) {
                        ++$totalCount;
                    }
                }
            } elseif ($this->matcherFactory->isMatcher($type)) {
                $isTypeSupported = true;
                $type = $this->matcherFactory->adapt($type);

                foreach ($traversableEvents as $event) {
                    if ($event instanceof ReceivedExceptionEventInterface) {
                        ++$totalCount;

                        if ($type->matches($event->exception())) {
                            $matchingEvents[] = $event;
                            ++$matchCount;
                        }
                    } elseif ($event instanceof ReceivedEventInterface) {
                        ++$totalCount;
                    }
                }
            }
        }

        if (!$isTypeSupported) {
            throw new InvalidArgumentException(
                sprintf(
                    'Unable to match exceptions against %s.',
                    $this->assertionRenderer->renderValue($type)
                )
            );
        }

        if ($cardinality->matches($matchCount, $totalCount)) {
            return $this->assertionRecorder->createSuccess($matchingEvents);
        }
    }

    /**
     * Throws an exception unless this call received an exception of the
     * supplied type.
     *
     * @param Exception|Error|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return EventCollectionInterface The result.
     * @throws Exception                If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function receivedException($type = null)
    {
        $cardinality = $this->cardinality;

        if ($result = $this->checkReceivedException($type)) {
            return $result;
        }

        if (null === $type) {
            $renderedType = 'generator to receive exception';
        } elseif (is_string($type)) {
            $renderedType = sprintf('generator to receive %s exception', $type);
        } elseif (is_object($type)) {
            if ($type instanceof Throwable || $type instanceof Exception) {
                $renderedType = sprintf(
                    'generator to receive exception equal to %s',
                    $this->assertionRenderer->renderException($type)
                );
            } elseif ($this->matcherFactory->isMatcher($type)) {
                $renderedType = sprintf(
                    'generator to receive exception like %s',
                    $this->matcherFactory->adapt($type)->describe()
                );
            }
        }

        if ($this->call->traversableEvents()) {
            $renderedProduced = sprintf(
                ":\n%s",
                $this->assertionRenderer->renderProduced($this->call)
            );
        } else {
            $renderedProduced = ' nothing.';
        }

        return $this->assertionRecorder->createFailure(
            sprintf(
                'Expected %s. Produced%s',
                $this->assertionRenderer
                    ->renderCardinality($cardinality, $renderedType),
                $renderedProduced
            )
        );
    }

    /**
     * Return match details only if the supplied check result is true.
     *
     * This is a convenience method for checks involving singular events.
     *
     * @param EventInterface|null $event       The event.
     * @param boolean             $checkResult The check result.
     *
     * @return tuple<array<EventInterface>> The match details.
     */
    protected function matchIf(EventInterface $event = null, $checkResult)
    {
        if ($checkResult && $event) {
            $matchCount = 1;
            $matchingEvents = array($event);
        } else {
            $matchCount = 0;
            $matchingEvents = array();
        }

        return array($matchCount, $matchingEvents);
    }

    private $call;
    private $matcherFactory;
    private $matcherVerifier;
    private $assertionRecorder;
    private $assertionRenderer;
    private $invocableInspector;
    private $argumentCount;
}
