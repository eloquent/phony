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
use Eloquent\Phony\Call\Event\CallEventInterface;
use Eloquent\Phony\Call\Event\CalledEventInterface;
use Eloquent\Phony\Call\Event\GeneratorEventInterface;
use Eloquent\Phony\Call\Event\ResponseEventInterface;
use Eloquent\Phony\Call\Event\YieldedEventInterface;
use Eloquent\Phony\Cardinality\Verification\AbstractCardinalityVerifier;
use Eloquent\Phony\Event\EventInterface;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\InvocableInspectorInterface;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Factory\MatcherFactoryInterface;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Matcher\Verification\MatcherVerifierInterface;
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
     * Returns true if this collection contains any events.
     *
     * @return boolean True if this collection contains any events.
     */
    public function hasEvents()
    {
        return $this->call->hasEvents();
    }

    /**
     * Get the first event.
     *
     * @return EventInterface|null The first event, or null if there are no events.
     */
    public function firstEvent()
    {
        return $this->call->firstEvent();
    }

    /**
     * Get the last event.
     *
     * @return EventInterface|null The last event, or null if there are no events.
     */
    public function lastEvent()
    {
        return $this->call->lastEvent();
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
     * Checks if called with the supplied arguments.
     *
     * @param mixed $argument,... The arguments.
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
     * @param mixed $argument,... The arguments.
     *
     * @return mixed                                The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     * @throws Exception                            If the assertion fails.
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
     * @return mixed                                The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     * @throws Exception                            If the assertion fails.
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

            throw $this->assertionRecorder->createFailure(
                sprintf($message, $value->describe(), $renderedThisValue)
            );
        }

        if ($cardinality->isNever()) {
            $message = 'Called on supplied object. Object was %s.';
        } else {
            $message = 'Not called on supplied object. Object was %s.';
        }

        throw $this->assertionRecorder
            ->createFailure(sprintf($message, $renderedThisValue));
    }

    /**
     * Checks if this call returned the supplied value.
     *
     * When called with no arguments, this method simply checks that the call
     * returned.
     *
     * @param mixed $value The value.
     *
     * @return EventCollectionInterface|null        The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     */
    public function checkReturned($value = null)
    {
        $cardinality = $this->resetCardinality()->assertSingular();

        $responseEvent = $this->call->responseEvent();
        $returnValue = $this->call->returnValue();
        $exception = $this->call->exception();

        if (0 === func_num_args()) {
            list($matchCount, $matchingEvents) =
                $this->matchIf($responseEvent, $responseEvent && !$exception);

            if ($cardinality->matches($matchCount, 1)) {
                return $this->assertionRecorder->createSuccess($matchingEvents);
            }

            return;
        }

        $value = $this->matcherFactory->adapt($value);

        list($matchCount, $matchingEvents) = $this->matchIf(
            $responseEvent,
            $responseEvent && !$exception && $value->matches($returnValue)
        );

        if ($cardinality->matches($matchCount, 1)) {
            return $this->assertionRecorder->createSuccess($matchingEvents);
        }
    }

    /**
     * Throws an exception unless this call returned the supplied value.
     *
     * When called with no arguments, this method simply checks that the call
     * returned.
     *
     * @param mixed $value The value.
     *
     * @return mixed                                The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     * @throws Exception                            If the assertion fails.
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

        throw $this->assertionRecorder->createFailure(
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
     * When called with no arguments, this method simply checks that the call
     * threw.
     *
     * @param Exception|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return EventCollectionInterface|null        The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     * @throws InvalidArgumentException             If the type is invalid.
     */
    public function checkThrew($type = null)
    {
        $cardinality = $this->resetCardinality()->assertSingular();

        $responseEvent = $this->call->responseEvent();
        $exception = $this->call->exception();
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
            if ($type instanceof Exception) {
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
     * When called with no arguments, this method simply checks that the call
     * threw.
     *
     * @param Exception|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return mixed                                The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     * @throws InvalidArgumentException             If the type is invalid.
     * @throws Exception                            If the assertion fails.
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
            $renderedType = sprintf(
                '%s exception',
                $this->assertionRenderer->renderValue($type)
            );
        } elseif (is_object($type)) {
            if ($type instanceof Exception) {
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

        throw $this->assertionRecorder->createFailure(
            sprintf(
                'Expected %s. %s',
                $this->assertionRenderer
                    ->renderCardinality($cardinality, $renderedType),
                $this->assertionRenderer->renderResponse($this->call)
            )
        );
    }

    /**
     * Checks if this call yielded the supplied values.
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
     * @return EventCollectionInterface|null The result.
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
     * @return mixed     The result.
     * @throws Exception If the assertion fails.
     */
    public function yielded($keyOrValue = null, $value = null)
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
                call_user_func_array(array($this, 'checkYielded'), $arguments)
        ) {
            return $result;
        }

        if (0 === $argumentCount) {
            $renderedType = 'call to yield';
        } elseif (1 === $argumentCount) {
            $renderedType = sprintf('yield to be like %s', $value->describe());
        } else {
            $renderedType = sprintf(
                'yield to be like %s => %s',
                $key->describe(),
                $value->describe()
            );
        }

        if ($this->call->generatorEvents()) {
            $renderedGenerated = sprintf(
                ":\n%s",
                $this->assertionRenderer->renderGenerated($this->call)
            );
        } else {
            $renderedGenerated = ' nothing.';
        }

        throw $this->assertionRecorder->createFailure(
            sprintf(
                'Expected %s. Generated%s',
                $this->assertionRenderer
                    ->renderCardinality($cardinality, $renderedType),
                $renderedGenerated
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
     * @return tuple<integer,array<integer,EventInterface>> The match details.
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
