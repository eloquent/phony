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
use Eloquent\Phony\Call\Event\CalledEventInterface;
use Eloquent\Phony\Call\Event\CallEventInterface;
use Eloquent\Phony\Call\Event\GeneratorEventInterface;
use Eloquent\Phony\Call\Event\ResponseEventInterface;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\InvocableInspectorInterface;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Factory\MatcherFactoryInterface;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Matcher\Verification\MatcherVerifierInterface;
use Exception;

/**
 * Provides convenience methods for verifying the details of a call.
 *
 * @internal
 */
class CallVerifier implements CallVerifierInterface
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
     * Get the sequence number.
     *
     * @return integer The sequence number.
     */
    public function sequenceNumber()
    {
        return $this->call->sequenceNumber();
    }

    /**
     * Get the time at which the call was made.
     *
     * @return float The time at which the call was made, in seconds since the Unix epoch.
     */
    public function startTime()
    {
        return $this->call->startTime();
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

        return $endTime - $this->call->startTime();
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

        return $responseTime - $this->call->startTime();
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
     * @return boolean True if called with the supplied arguments.
     */
    public function calledWith()
    {
        $matchers = $this->matcherFactory->adaptAll(func_get_args());
        $matchers[] = $this->matcherFactory->wildcard();

        return $this->matcherVerifier
            ->matches($matchers, $this->call->arguments());
    }

    /**
     * Throws an exception unless called with the supplied arguments (and
     * possibly others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @throws Exception If the assertion fails.
     */
    public function assertCalledWith()
    {
        $matchers = $this->matcherFactory->adaptAll(func_get_args());
        $matchers[] = $this->matcherFactory->wildcard();
        $arguments = $this->call->arguments();

        if (!$this->matcherVerifier->matches($matchers, $arguments)) {
            throw $this->assertionRecorder->createFailure(
                sprintf(
                    "Expected arguments like:\n    %s\n" .
                        "Actual arguments:\n    %s",
                    $this->assertionRenderer->renderMatchers($matchers),
                    $this->assertionRenderer->renderArguments($arguments)
                )
            );
        }

        $this->assertionRecorder->recordSuccess();
    }

    /**
     * Returns true if called with the supplied arguments (and no others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return boolean True if called with the supplied arguments.
     */
    public function calledWithExactly()
    {
        return $this->matcherVerifier->matches(
            $this->matcherFactory->adaptAll(func_get_args()),
            $this->call->arguments()
        );
    }

    /**
     * Throws an exception unless called with the supplied arguments (and no
     * others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @throws Exception If the assertion fails.
     */
    public function assertCalledWithExactly()
    {
        $matchers = $this->matcherFactory->adaptAll(func_get_args());
        $arguments = $this->call->arguments();

        if (!$this->matcherVerifier->matches($matchers, $arguments)) {
            throw $this->assertionRecorder->createFailure(
                sprintf(
                    "Expected arguments like:\n    %s\n" .
                        "Actual arguments:\n    %s",
                    $this->assertionRenderer->renderMatchers($matchers),
                    $this->assertionRenderer->renderArguments($arguments)
                )
            );
        }

        $this->assertionRecorder->recordSuccess();
    }

    /**
     * Returns true if not called with the supplied arguments (and possibly
     * others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return boolean True if not called with the supplied arguments.
     */
    public function notCalledWith()
    {
        $matchers = $this->matcherFactory->adaptAll(func_get_args());
        $matchers[] = $this->matcherFactory->wildcard();

        return !$this->matcherVerifier
            ->matches($matchers, $this->call->arguments());
    }

    /**
     * Throws an exception unless not called with the supplied arguments (and
     * possibly others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @throws Exception If the assertion fails.
     */
    public function assertNotCalledWith()
    {
        $matchers = $this->matcherFactory->adaptAll(func_get_args());
        $matchers[] = $this->matcherFactory->wildcard();
        $arguments = $this->call->arguments();

        if ($this->matcherVerifier->matches($matchers, $arguments)) {
            throw $this->assertionRecorder->createFailure(
                sprintf(
                    "Expected arguments unlike:\n    %s\n" .
                        "Actual arguments:\n    %s",
                    $this->assertionRenderer->renderMatchers($matchers),
                    $this->assertionRenderer->renderArguments($arguments)
                )
            );
        }

        $this->assertionRecorder->recordSuccess();
    }

    /**
     * Returns true if not called with the supplied arguments (and no others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return boolean True if not called with the supplied arguments.
     */
    public function notCalledWithExactly()
    {
        return !$this->matcherVerifier->matches(
            $this->matcherFactory->adaptAll(func_get_args()),
            $this->call->arguments()
        );
    }

    /**
     * Throws an exception unless not called with the supplied arguments (and no
     * others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @throws Exception If the assertion fails.
     */
    public function assertNotCalledWithExactly()
    {
        $matchers = $this->matcherFactory->adaptAll(func_get_args());
        $arguments = $this->call->arguments();

        if ($this->matcherVerifier->matches($matchers, $arguments)) {
            throw $this->assertionRecorder->createFailure(
                sprintf(
                    "Expected arguments unlike:\n    %s\n" .
                        "Actual arguments:\n    %s",
                    $this->assertionRenderer->renderMatchers($matchers),
                    $this->assertionRenderer->renderArguments($arguments)
                )
            );
        }

        $this->assertionRecorder->recordSuccess();
    }

    /**
     * Returns true if this call occurred before the supplied call.
     *
     * @param CallInterface $call Another call.
     *
     * @return boolean True if this call occurred before the supplied call.
     */
    public function calledBefore(CallInterface $call)
    {
        return $call->sequenceNumber() > $this->call->sequenceNumber();
    }

    /**
     * Throws an exception unless this call occurred before the supplied call.
     *
     * @param CallInterface $call Another call.
     *
     * @throws Exception If the assertion fails.
     */
    public function assertCalledBefore(CallInterface $call)
    {
        if ($call->sequenceNumber() <= $this->call->sequenceNumber()) {
            throw $this->assertionRecorder
                ->createFailure('Not called before supplied call.');
        }

        $this->assertionRecorder->recordSuccess();
    }

    /**
     * Returns true if this call occurred after the supplied call.
     *
     * @param CallInterface $call Another call.
     *
     * @return boolean True if this call occurred after the supplied call.
     */
    public function calledAfter(CallInterface $call)
    {
        return $call->sequenceNumber() < $this->call->sequenceNumber();
    }

    /**
     * Throws an exception unless this call occurred after the supplied call.
     *
     * @param CallInterface $call Another call.
     *
     * @throws Exception If the assertion fails.
     */
    public function assertCalledAfter(CallInterface $call)
    {
        if ($call->sequenceNumber() >= $this->call->sequenceNumber()) {
            throw $this->assertionRecorder
                ->createFailure('Not called after supplied call.');
        }

        $this->assertionRecorder->recordSuccess();
    }

    /**
     * Returns true if the $this value is equal to the supplied value.
     *
     * @param object|null $value The possible $this value.
     *
     * @return boolean True if the $this value is equal to the supplied value.
     */
    public function calledOn($value)
    {
        $thisValue = $this->invocableInspector
            ->callbackThisValue($this->call->callback());

        if ($this->matcherFactory->isMatcher($value)) {
            return $this->matcherFactory->adapt($value)->matches($thisValue);
        }

        return $thisValue === $value;
    }

    /**
     * Throws an exception unless the $this value is equal to the supplied
     * value.
     *
     * @param object|null $value The possible $this value.
     *
     * @throws Exception If the assertion fails.
     */
    public function assertCalledOn($value)
    {
        $thisValue = $this->invocableInspector
            ->callbackThisValue($this->call->callback());

        if ($this->matcherFactory->isMatcher($value)) {
            $value = $this->matcherFactory->adapt($value);

            if (!$value->matches($thisValue)) {
                throw $this->assertionRecorder->createFailure(
                    sprintf(
                        'Not called on object like %s. Actual object was %s.',
                        $value->describe(),
                        $this->assertionRenderer->renderValue($thisValue)
                    )
                );
            }
        } elseif ($thisValue !== $value) {
            throw $this->assertionRecorder->createFailure(
                sprintf(
                    'Not called on expected object. Actual object was %s.',
                    $this->assertionRenderer->renderValue($thisValue)
                )
            );
        }

        $this->assertionRecorder->recordSuccess();
    }

    /**
     * Returns true if this call returned the supplied value.
     *
     * @param mixed $value The value.
     *
     * @return boolean True if this call returned the supplied value.
     */
    public function returned($value)
    {
        return $this->matcherFactory->adapt($value)
            ->matches($this->call->returnValue());
    }

    /**
     * Throws an exception unless this call returned the supplied value.
     *
     * @param mixed $value The value.
     *
     * @throws Exception If the assertion fails.
     */
    public function assertReturned($value)
    {
        $value = $this->matcherFactory->adapt($value);
        $returnValue = $this->call->returnValue();

        if (!$value->matches($returnValue)) {
            throw $this->assertionRecorder->createFailure(
                sprintf(
                    'Expected return value like %s. Returned %s.',
                    $value->describe(),
                    $this->assertionRenderer->renderValue($returnValue)
                )
            );
        }

        $this->assertionRecorder->recordSuccess();
    }

    /**
     * Returns true if an exception of the supplied type was thrown.
     *
     * @param Exception|string|null $type An exception like, the type of exception, or null for any exception.
     *
     * @return boolean True if a matching exception was thrown.
     */
    public function threw($type = null)
    {
        $exception = $this->call->exception();

        if (is_object($type) && $this->matcherFactory->isMatcher($type)) {
            return $this->matcherFactory->adapt($type)->matches($exception);
        }

        if (null === $exception) {
            return false;
        }

        if (null === $type) {
            return true;
        }

        if (is_string($type)) {
            return is_a($exception, $type);
        }

        return $type instanceof Exception && $exception == $type;
    }

    /**
     * Throws an exception unless this call threw an exception of the supplied
     * type.
     *
     * @param Exception|string|null $type An exception like, the type of exception, or null for any exception.
     *
     * @throws Exception If the assertion fails.
     */
    public function assertThrew($type = null)
    {
        $exception = $this->call->exception();

        if (null === $type) {
            if (null === $exception) {
                throw $this->assertionRecorder
                    ->createFailure('Nothing thrown.');
            }

            return $this->assertionRecorder->recordSuccess();
        } elseif (is_string($type)) {
            if (is_a($exception, $type)) {
                return $this->assertionRecorder->recordSuccess();
            } elseif (null === $exception) {
                throw $this->assertionRecorder->createFailure(
                    sprintf(
                        'Expected %s exception. Nothing thrown.',
                        $this->assertionRenderer->renderValue($type)
                    )
                );
            }

            throw $this->assertionRecorder->createFailure(
                sprintf(
                    'Expected %s exception. Threw %s.',
                    $this->assertionRenderer->renderValue($type),
                    $this->assertionRenderer->renderException($exception)
                )
            );
        } elseif (is_object($type)) {
            if ($type instanceof Exception) {
                if ($exception == $type) {
                    return $this->assertionRecorder->recordSuccess();
                } elseif (null === $exception) {
                    throw $this->assertionRecorder->createFailure(
                        sprintf(
                            'Expected exception equal to %s. Nothing thrown.',
                            $this->assertionRenderer->renderException($type)
                        )
                    );
                }

                throw $this->assertionRecorder->createFailure(
                    sprintf(
                        'Expected exception equal to %s. Threw %s.',
                        $this->assertionRenderer->renderException($type),
                        $this->assertionRenderer->renderException($exception)
                    )
                );
            } elseif ($this->matcherFactory->isMatcher($type)) {
                $type = $this->matcherFactory->adapt($type);

                if ($type->matches($exception)) {
                    return $this->assertionRecorder->recordSuccess();
                }

                throw $this->assertionRecorder->createFailure(
                    sprintf(
                        'Expected exception like %s. Threw %s.',
                        $type->describe(),
                        $this->assertionRenderer->renderException($exception)
                    )
                );
            }
        }

        throw $this->assertionRecorder->createFailure(
            sprintf(
                'Unable to match exceptions against %s.',
                $this->assertionRenderer->renderValue($type)
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
