<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call;

use Eloquent\Phony\Assertion\Recorder\AssertionRecorder;
use Eloquent\Phony\Assertion\Recorder\AssertionRecorderInterface;
use Eloquent\Phony\Assertion\Renderer\AssertionRenderer;
use Eloquent\Phony\Assertion\Renderer\AssertionRendererInterface;
use Eloquent\Phony\Assertion\Result\AssertionResultInterface;
use Eloquent\Phony\Call\Event\CallEventInterface;
use Eloquent\Phony\Call\Event\CalledEventInterface;
use Eloquent\Phony\Call\Event\GeneratorEventInterface;
use Eloquent\Phony\Call\Event\ResponseEventInterface;
use Eloquent\Phony\Call\Event\YieldedEventInterface;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\InvocableInspectorInterface;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Factory\MatcherFactoryInterface;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Matcher\Verification\MatcherVerifierInterface;
use Eloquent\Phony\Verification\AbstractCardinalityVerifier;
use Exception;
use InvalidArgumentException;

/**
 * Provides convenience methods for verifying the details of a call.
 *
 * @internal
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
     * Add a generator event.
     *
     * @param GeneratorEventInterface $generatorEvent The generator event.
     *
     * @throws InvalidArgumentException If the call has already completed.
     */
    public function addGeneratorEvent(GeneratorEventInterface $generatorEvent)
    {
        $this->call->addGeneratorEvent($generatorEvent);
    }

    /**
     * Get the generator events.
     *
     * @return array<integer,GeneratorEventInterface> The generator events.
     */
    public function generatorEvents()
    {
        return $this->call->generatorEvents();
    }

    /**
     * Set the end event.
     *
     * @param ResponseEventInterface $endEvent The end event.
     *
     * @throws InvalidArgumentException If the call has already completed.
     */
    public function setEndEvent(ResponseEventInterface $endEvent)
    {
        $this->call->setEndEvent($endEvent);
    }

    /**
     * Get the end event.
     *
     * @return ResponseEventInterface|null The end event, or null if the call has not yet completed.
     */
    public function endEvent()
    {
        return $this->call->endEvent();
    }

    /**
     * Get all events.
     *
     * @return array<integer,CallEventInterface> The events.
     */
    public function events()
    {
        return $this->call->events();
    }

    /**
     * Returns true if this call has responded.
     *
     * @return boolean True if this call has responded.
     */
    public function hasResponded()
    {
        return $this->call->hasResponded();
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
     * @return array<integer,mixed> The received arguments.
     */
    public function arguments()
    {
        return $this->call->arguments();
    }

    /**
     * Get the return value.
     *
     * @return mixed The return value.
     */
    public function returnValue()
    {
        return $this->call->returnValue();
    }

    /**
     * Get the thrown exception.
     *
     * @return Exception|null The thrown exception, or null if no exception was thrown.
     */
    public function exception()
    {
        return $this->call->exception();
    }

    /**
     * Get the time at which the call responded.
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
            return null;
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
            return null;
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
     * Returns true if called with the supplied arguments (and possibly others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return boolean                              The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     */
    public function checkCalledWith()
    {
        $matchers = $this->matcherFactory->adaptAll(func_get_args());
        $matchers[] = $this->matcherFactory->wildcard();

        return $this->doCheckCalledWith($matchers);
    }

    /**
     * Throws an exception unless called with the supplied arguments (and
     * possibly others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return AssertionResultInterface             The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     * @throws Exception                            If the assertion fails.
     */
    public function calledWith()
    {
        $matchers = $this->matcherFactory->adaptAll(func_get_args());
        $matchers[] = $this->matcherFactory->wildcard();

        return $this->doCalledWith($matchers);
    }

    /**
     * Returns true if called with the supplied arguments (and no others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return boolean                              The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     */
    public function checkCalledWithExactly()
    {
        return $this->doCheckCalledWith(
            $this->matcherFactory->adaptAll(func_get_args())
        );
    }

    /**
     * Throws an exception unless called with the supplied arguments (and no
     * others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return AssertionResultInterface             The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     * @throws Exception                            If the assertion fails.
     */
    public function calledWithExactly()
    {
        return $this
            ->doCalledWith($this->matcherFactory->adaptAll(func_get_args()));
    }

    /**
     * Returns true if this call occurred before the supplied call.
     *
     * @param CallInterface $call Another call.
     *
     * @return boolean                              The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     */
    public function checkCalledBefore(CallInterface $call)
    {
        return $this->resetCardinality()->assertSingluar()->matches(
            $call->sequenceNumber() > $this->call->sequenceNumber()
        );
    }

    /**
     * Throws an exception unless this call occurred before the supplied call.
     *
     * @param CallInterface $call Another call.
     *
     * @return AssertionResultInterface             The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     * @throws Exception                            If the assertion fails.
     */
    public function calledBefore(CallInterface $call)
    {
        $cardinality = $this->resetCardinality()->assertSingluar();

        list($matchCount, $matchingEvents) = $this->matchIf(
            $this->call,
            $call->sequenceNumber() > $this->call->sequenceNumber()
        );

        if ($cardinality->matches($matchCount)) {
            return $this->assertionRecorder->createSuccess($matchingEvents);
        }

        if ($cardinality->isNever()) {
            throw $this->assertionRecorder
                ->createFailure('Called before supplied call.');
        } else {
            throw $this->assertionRecorder
                ->createFailure('Not called before supplied call.');
        }
    }

    /**
     * Returns true if this call occurred after the supplied call.
     *
     * @param CallInterface $call Another call.
     *
     * @return boolean                              The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     */
    public function checkCalledAfter(CallInterface $call)
    {
        return $this->resetCardinality()->assertSingluar()->matches(
            $call->sequenceNumber() < $this->call->sequenceNumber()
        );
    }

    /**
     * Throws an exception unless this call occurred after the supplied call.
     *
     * @param CallInterface $call Another call.
     *
     * @return AssertionResultInterface             The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     * @throws Exception                            If the assertion fails.
     */
    public function calledAfter(CallInterface $call)
    {
        $cardinality = $this->resetCardinality()->assertSingluar();

        list($matchCount, $matchingEvents) = $this->matchIf(
            $this->call,
            $call->sequenceNumber() < $this->call->sequenceNumber()
        );

        if ($cardinality->matches($matchCount)) {
            return $this->assertionRecorder->createSuccess($matchingEvents);
        }

        if ($cardinality->isNever()) {
            throw $this->assertionRecorder
                ->createFailure('Called after supplied call.');
        } else {
            throw $this->assertionRecorder
                ->createFailure('Not called after supplied call.');
        }
    }

    /**
     * Returns true if the $this value is equal to the supplied value.
     *
     * @param object|null $value The possible $this value.
     *
     * @return boolean                              The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     */
    public function checkCalledOn($value)
    {
        $cardinality = $this->resetCardinality()->assertSingluar();

        $thisValue = $this->invocableInspector
            ->callbackThisValue($this->call->callback());

        if ($this->matcherFactory->isMatcher($value)) {
            $isMatch = $this->matcherFactory->adapt($value)
                ->matches($thisValue);
        } else {
            $isMatch = $thisValue === $value;
        }

        return $cardinality->matches($isMatch);
    }

    /**
     * Throws an exception unless the $this value is equal to the supplied
     * value.
     *
     * @param object|null $value The possible $this value.
     *
     * @return AssertionResultInterface             The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     * @throws Exception                            If the assertion fails.
     */
    public function calledOn($value)
    {
        $cardinality = $this->resetCardinality()->assertSingluar();

        $thisValue = $this->invocableInspector
            ->callbackThisValue($this->call->callback());

        if ($this->matcherFactory->isMatcher($value)) {
            $value = $this->matcherFactory->adapt($value);

            list($matchCount, $matchingEvents) =
                $this->matchIf($this->call, $value->matches($thisValue));

            if ($cardinality->matches($matchCount)) {
                return $this->assertionRecorder->createSuccess($matchingEvents);
            }

            if ($cardinality->isNever()) {
                $message = 'Called on object like %s. Object was %s.';
            } else {
                $message = 'Not called on object like %s. Object was %s.';
            }

            throw $this->assertionRecorder->createFailure(
                sprintf(
                    $message,
                    $value->describe(),
                    $this->assertionRenderer->renderValue($thisValue)
                )
            );
        }

        list($matchCount, $matchingEvents) =
            $this->matchIf($this->call, $thisValue === $value);

        if ($cardinality->matches($matchCount)) {
            return $this->assertionRecorder->createSuccess($matchingEvents);
        }

        if ($cardinality->isNever()) {
            $message = 'Called on unexpected object. Object was %s.';
        } else {
            $message = 'Not called on expected object. Object was %s.';
        }

        throw $this->assertionRecorder->createFailure(
            sprintf($message, $this->assertionRenderer->renderValue($thisValue))
        );
    }

    /**
     * Returns true if this call returned the supplied value.
     *
     * When called with no arguments, this method simply checks that the call
     * returned.
     *
     * @param mixed $value The value.
     *
     * @return boolean                              The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     */
    public function checkReturned($value = null)
    {
        $cardinality = $this->resetCardinality()->assertSingluar();

        if (!$this->call->hasResponded() || $this->call->exception()) {
            $isMatch = false;
        } else {
            $isMatch = 0 === func_num_args() ||
                $this->matcherFactory->adapt($value)
                    ->matches($this->call->returnValue());
        }

        return $cardinality->matches($isMatch);
    }

    /**
     * Throws an exception unless this call returned the supplied value.
     *
     * When called with no arguments, this method simply checks that the call
     * returned.
     *
     * @param mixed $value The value.
     *
     * @return AssertionResultInterface             The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     * @throws Exception                            If the assertion fails.
     */
    public function returned($value = null)
    {
        $cardinality = $this->resetCardinality()->assertSingluar();

        $responseEvent = $this->call->responseEvent();
        $returnValue = $this->call->returnValue();
        $exception = $this->call->exception();

        if (0 === func_num_args()) {
            list($matchCount, $matchingEvents) =
                $this->matchIf($responseEvent, $responseEvent && !$exception);

            if ($cardinality->matches($matchCount)) {
                return $this->assertionRecorder->createSuccess($matchingEvents);
            }

            if ($cardinality->isNever()) {
                $message = 'Expected no return. ';
            } else {
                $message = 'Expected return. ';
            }
        } else {
            $value = $this->matcherFactory->adapt($value);

            list($matchCount, $matchingEvents) = $this->matchIf(
                $responseEvent,
                $responseEvent && !$exception && $value->matches($returnValue)
            );

            if ($cardinality->matches($matchCount)) {
                return $this->assertionRecorder->createSuccess($matchingEvents);
            }

            if ($cardinality->isNever()) {
                $message =
                    sprintf('Expected no return like %s. ', $value->describe());
            } else {
                $message =
                    sprintf('Expected return like %s. ', $value->describe());
            }
        }

        throw $this->assertionRecorder->createFailure(
            $message . $this->assertionRenderer->renderResponse($this->call)
        );
    }

    /**
     * Returns true if an exception of the supplied type was thrown.
     *
     * When called with no arguments, this method simply checks that the call
     * threw.
     *
     * @param Exception|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return boolean                              The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     * @throws InvalidArgumentException             If the type is invalid.
     */
    public function checkThrew($type = null)
    {
        $cardinality = $this->resetCardinality()->assertSingluar();

        $exception = $this->call->exception();
        $isTypeSupported = true;

        if (!$exception) {
            $isMatch = false;
        } elseif (null === $type) {
            $isMatch = true;
        } elseif (is_string($type)) {
            $isMatch = is_a($exception, $type);
        } elseif (is_object($type)) {
            if ($type instanceof Exception) {
                $isMatch = $exception == $type;
            } elseif ($this->matcherFactory->isMatcher($type)) {
                $isMatch = $this->matcherFactory->adapt($type)
                    ->matches($exception);
            } else {
                $isTypeSupported = false;
            }
        } else {
            $isTypeSupported = false;
        }

        if (!$isTypeSupported) {
            throw new InvalidArgumentException(
                sprintf(
                    'Unable to match exceptions against %s.',
                    $this->assertionRenderer->renderValue($type)
                )
            );
        }

        return $cardinality->matches($isMatch);
    }

    /**
     * Throws an exception unless this call threw an exception of the supplied
     * type.
     *
     * When called with no arguments, this method simply checks that the call
     * threw.
     *
     * @param Exception|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return AssertionResultInterface             The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     * @throws InvalidArgumentException             If the type is invalid.
     * @throws Exception                            If the assertion fails.
     */
    public function threw($type = null)
    {
        $cardinality = $this->resetCardinality()->assertSingluar();

        $responseEvent = $this->call->responseEvent();
        $exception = $this->call->exception();
        $isTypeSupported = true;

        if (null === $type) {
            list($matchCount, $matchingEvents) =
                $this->matchIf($responseEvent, $exception);

            if ($cardinality->matches($matchCount)) {
                return $this->assertionRecorder->createSuccess($matchingEvents);
            }

            if ($cardinality->isNever()) {
                $message = 'Expected no exception. ';
            } else {
                $message = 'Expected exception. ';
            }
        } elseif (is_string($type)) {
            list($matchCount, $matchingEvents) =
                $this->matchIf($responseEvent, is_a($exception, $type));

            if ($cardinality->matches($matchCount)) {
                return $this->assertionRecorder->createSuccess($matchingEvents);
            }

            if ($cardinality->isNever()) {
                $message = sprintf(
                    'Expected no %s exception. ',
                    $this->assertionRenderer->renderValue($type)
                );
            } else {
                $message = sprintf(
                    'Expected %s exception. ',
                    $this->assertionRenderer->renderValue($type)
                );
            }
        } elseif (is_object($type)) {
            if ($type instanceof Exception) {
                list($matchCount, $matchingEvents) =
                    $this->matchIf($responseEvent, $exception == $type);

                if (
                    $cardinality->matches($matchCount)
                ) {
                    return $this->assertionRecorder
                        ->createSuccess($matchingEvents);
                }

                if ($cardinality->isNever()) {
                    $message = sprintf(
                        'Expected no exception equal to %s. ',
                        $this->assertionRenderer->renderException($type)
                    );
                } else {
                    $message = sprintf(
                        'Expected exception equal to %s. ',
                        $this->assertionRenderer->renderException($type)
                    );
                }
            } elseif ($this->matcherFactory->isMatcher($type)) {
                $type = $this->matcherFactory->adapt($type);
                list($matchCount, $matchingEvents) =
                    $this->matchIf($responseEvent, $type->matches($exception));

                if (
                    $cardinality->matches($matchCount)
                ) {
                    return $this->assertionRecorder
                        ->createSuccess($matchingEvents);
                }

                if ($cardinality->isNever()) {
                    $message = sprintf(
                        'Expected no exception like %s. ',
                        $type->describe()
                    );
                } else {
                    $message = sprintf(
                        'Expected exception like %s. ',
                        $type->describe()
                    );
                }
            } else {
                $isTypeSupported = false;
            }
        } else {
            $isTypeSupported = false;
        }

        if (!$isTypeSupported) {
            throw new InvalidArgumentException(
                sprintf(
                    'Unable to match exceptions against %s.',
                    $this->assertionRenderer->renderValue($type)
                )
            );
        }

        throw $this->assertionRecorder->createFailure(
            $message . $this->assertionRenderer->renderResponse($this->call)
        );
    }

    /**
     * Returns true if this call yielded the supplied values.
     *
     * When called with no arguments, this method simply checks that the call
     * yielded.
     *
     * With a single argument, it checks that a value matching the argument was
     * yielded.
     *
     * With two arguments, it checks that a key and value matching the
     * respective arguments were yielded together.
     *
     * @param mixed $keyOrValue The key or value.
     * @param mixed $value      The value.
     *
     * @return boolean The result.
     */
    public function checkYielded($keyOrValue = null, $value = null)
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

        $matchCount = 0;
        $totalCount = 0;

        foreach ($this->call->events() as $event) {
            if ($event instanceof YieldedEventInterface) {
                $totalCount++;

                if ($checkKey && !$key->matches($event->key())) {
                    continue;
                }
                if ($checkValue && !$value->matches($event->value())) {
                    continue;
                }

                $matchCount++;
            }
        }

        return $cardinality->matches($matchCount, $totalCount);
    }

    /**
     * Throws an exception unless this call yielded the supplied values.
     *
     * When called with no arguments, this method simply checks that the call
     * yielded.
     *
     * With a single argument, it checks that a value matching the argument was
     * yielded.
     *
     * With two arguments, it checks that a key and value matching the
     * respective arguments were yielded together.
     *
     * @param mixed $keyOrValue The key or value.
     * @param mixed $value      The value.
     *
     * @return AssertionResultInterface The result.
     * @throws Exception                If the assertion fails.
     */
    public function yielded($keyOrValue = null, $value = null)
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

        foreach ($this->call->events() as $event) {
            if ($event instanceof YieldedEventInterface) {
                $totalCount++;

                if ($checkKey && !$key->matches($event->key())) {
                    continue;
                }
                if ($checkValue && !$value->matches($event->value())) {
                    continue;
                }

                $matchingEvents[] = $event;
                $matchCount++;
            }
        }

        if ($cardinality->matches($matchCount, $totalCount)) {
            return $this->assertionRecorder->createSuccess($matchingEvents);
        }

        $renderedCardinality =
            $this->assertionRenderer->renderCardinality($cardinality, 'yield');

        if (0 === $argumentCount) {
            $message = sprintf('Expected %s.', $renderedCardinality);
        } elseif (1 === $argumentCount) {
            $message = sprintf(
                'Expected %s like %s.',
                $renderedCardinality,
                $value->describe()
            );
        } else {
            $message = sprintf(
                'Expected %s like %s => %s.',
                $renderedCardinality,
                $key->describe(),
                $value->describe()
            );
        }

        if ($this->call->generatorEvents()) {
            $message .= sprintf(
                " Generated:\n%s",
                $this->assertionRenderer->renderGenerated($this->call)
            );
        } else {
            $message .= ' Generated nothing.';
        }

        throw $this->assertionRecorder->createFailure($message);
    }

    private function matchIf($event, $checkResult)
    {
        if ($checkResult) {
            $matchCount = 1;
            $matchingEvents = array($event);
        } else {
            $matchCount = 0;
            $matchingEvents = array();
        }

        return array($matchCount, $matchingEvents);
    }

    private function doCheckCalledWith(array $matchers)
    {
        return $this->resetCardinality()->assertSingluar()->matches(
            $this->matcherVerifier->matches($matchers, $this->call->arguments())
        );
    }

    private function doCalledWith(array $matchers)
    {
        $cardinality = $this->resetCardinality()->assertSingluar();

        list($matchCount, $matchingEvents) = $this->matchIf(
            $this->call,
            $this->matcherVerifier->matches($matchers, $this->call->arguments())
        );

        if ($cardinality->matches($matchCount)) {
            return $this->assertionRecorder->createSuccess($matchingEvents);
        }

        throw $this->assertionRecorder->createFailure(
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

    private $call;
    private $matcherFactory;
    private $matcherVerifier;
    private $assertionRecorder;
    private $assertionRenderer;
    private $invocableInspector;
    private $argumentCount;
}
