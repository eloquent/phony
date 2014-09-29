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

use Eloquent\Phony\Assertion\AssertionRecorder;
use Eloquent\Phony\Assertion\AssertionRecorderInterface;
use Eloquent\Phony\Assertion\Renderer\AssertionRenderer;
use Eloquent\Phony\Assertion\Renderer\AssertionRendererInterface;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Factory\MatcherFactoryInterface;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Matcher\Verification\MatcherVerifierInterface;
use Eloquent\Phony\Matcher\WildcardMatcher;
use Exception;
use ReflectionFunctionAbstract;

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
     * @param CallInterface                   $call              The call.
     * @param MatcherFactoryInterface|null    $matcherFactory    The matcher factory to use.
     * @param MatcherVerifierInterface|null   $matcherVerifier   The matcher verifier to use.
     * @param AssertionRecorderInterface|null $assertionRecorder The assertion recorder to use.
     * @param AssertionRendererInterface|null $assertionRenderer The assertion renderer to use.
     */
    public function __construct(
        CallInterface $call,
        MatcherFactoryInterface $matcherFactory = null,
        MatcherVerifierInterface $matcherVerifier = null,
        AssertionRecorderInterface $assertionRecorder = null,
        AssertionRendererInterface $assertionRenderer = null
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

        $this->call = $call;
        $this->matcherFactory = $matcherFactory;
        $this->matcherVerifier = $matcherVerifier;
        $this->assertionRecorder = $assertionRecorder;
        $this->assertionRenderer = $assertionRenderer;

        $this->duration = $call->endTime() - $call->startTime();
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
     * Get the function or method called.
     *
     * @return ReflectionFunctionAbstract The function or method called.
     */
    public function reflector()
    {
        return $this->call->reflector();
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
     * Get the time at which the call completed.
     *
     * @return float The time at which the call completed, in seconds since the Unix epoch.
     */
    public function endTime()
    {
        return $this->call->endTime();
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
     * Get the $this value.
     *
     * @return object The $this value.
     */
    public function thisValue()
    {
        return $this->call->thisValue();
    }

    /**
     * Get the call duration.
     *
     * @return float The call duration, in seconds.
     */
    public function duration()
    {
        return $this->duration;
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
        $matchers[] = WildcardMatcher::instance();

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
        $matchers[] = WildcardMatcher::instance();
        $arguments = $this->call->arguments();

        if (!$this->matcherVerifier->matches($matchers, $arguments)) {
            throw $this->assertionRecorder->createFailure(
                sprintf(
                    "Expected arguments to match:\n    %s\nThe actual " .
                        "arguments were:\n    %s",
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
                    "Expected arguments to match:\n    %s\nThe actual " .
                        "arguments were:\n    %s",
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
        $matchers[] = WildcardMatcher::instance();

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
        $matchers[] = WildcardMatcher::instance();
        $arguments = $this->call->arguments();

        if ($this->matcherVerifier->matches($matchers, $arguments)) {
            throw $this->assertionRecorder->createFailure(
                sprintf(
                    "Expected arguments not to match:\n    %s\nThe actual " .
                        "arguments were:\n    %s",
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
                    "Expected arguments not to match:\n    %s\nThe actual " .
                        "arguments were:\n    %s",
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
            throw $this->assertionRecorder->createFailure(
                'The call was not made before the supplied call.'
            );
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
            throw $this->assertionRecorder->createFailure(
                'The call was not made after the supplied call.'
            );
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
        if ($this->matcherFactory->isMatcher($value)) {
            return $this->matcherFactory->adapt($value)
                ->matches($this->call->thisValue());
        }

        return $this->call->thisValue() === $value;
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
        $thisValue = $this->call->thisValue();

        if ($this->matcherFactory->isMatcher($value)) {
            $value = $this->matcherFactory->adapt($value);

            if (!$value->matches($thisValue)) {
                throw $this->assertionRecorder->createFailure(
                    sprintf(
                        'The call was not made on an object that matches %s. ' .
                            'The actual object was %s.',
                        $value->describe(),
                        $this->assertionRenderer->renderValue($thisValue)
                    )
                );
            }
        } elseif ($thisValue !== $value) {
            throw $this->assertionRecorder->createFailure(
                sprintf(
                    'The call was not made on the expected object. The ' .
                        'actual object was %s.',
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
                    'The return value did not match %s. The actual return ' .
                        'value was %s.',
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
     * @param Exception|string|null $type An exception to match, the type of exception, or null for any exception.
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
     * @param Exception|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @throws Exception If the assertion fails.
     */
    public function assertThrew($type = null)
    {
        $exception = $this->call->exception();

        if (null === $type) {
            if (null === $exception) {
                throw $this->assertionRecorder->createFailure(
                    'Expected an exception, but no exception was thrown.'
                );
            } else {
                $this->assertionRecorder->recordSuccess();
            }
        } elseif (is_string($type)) {
            if (is_a($exception, $type)) {
                $this->assertionRecorder->recordSuccess();
            } elseif (null === $exception) {
                throw $this->assertionRecorder->createFailure(
                    sprintf(
                        'Expected an exception of type %s, but no exception ' .
                            'was thrown.',
                        $this->assertionRenderer->renderValue($type)
                    )
                );
            } else {
                throw $this->assertionRecorder->createFailure(
                    sprintf(
                        'Expected an exception of type %s. The actual ' .
                            'exception was %s(%s).',
                        $this->assertionRenderer->renderValue($type),
                        get_class($exception),
                        $this->assertionRenderer
                            ->renderValue($exception->getMessage())
                    )
                );
            }
        } elseif (is_object($type)) {
            if ($type instanceof Exception) {
                if ($exception == $type) {
                    $this->assertionRecorder->recordSuccess();
                } elseif (null === $exception) {
                    throw $this->assertionRecorder->createFailure(
                        sprintf(
                            'Expected an exception equal to %s(%s), but no ' .
                                'exception was thrown.',
                            get_class($type),
                            $this->assertionRenderer
                                ->renderValue($type->getMessage())
                        )
                    );
                } else {
                    throw $this->assertionRecorder->createFailure(
                        sprintf(
                            'Expected an exception equal to %s(%s). The ' .
                                'actual exception was %s(%s).',
                            get_class($type),
                            $this->assertionRenderer
                                ->renderValue($type->getMessage()),
                            get_class($exception),
                            $this->assertionRenderer
                                ->renderValue($exception->getMessage())
                        )
                    );
                }
            } elseif ($this->matcherFactory->isMatcher($type)) {
                $type = $this->matcherFactory->adapt($type);

                if ($type->matches($exception)) {
                    $this->assertionRecorder->recordSuccess();
                } else {
                    throw $this->assertionRecorder->createFailure(
                        sprintf(
                            'Expected an exception matching %s. The actual ' .
                                'exception was %s(%s).',
                            $type->describe(),
                            get_class($exception),
                            $this->assertionRenderer
                                ->renderValue($exception->getMessage())
                        )
                    );
                }
            }
        } else {
            throw $this->assertionRecorder->createFailure(
                sprintf(
                    'Unable to match exceptions against %s.',
                    $this->assertionRenderer->renderValue($type)
                )
            );
        }
    }

    private $call;
    private $matcherFactory;
    private $matcherVerifier;
    private $assertionRecorder;
    private $assertionRenderer;
    private $duration;
    private $argumentCount;
}
