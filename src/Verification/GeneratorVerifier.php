<?php

declare(strict_types=1);

namespace Eloquent\Phony\Verification;

use Eloquent\Phony\Assertion\AssertionRecorder;
use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Call\Call;
use Eloquent\Phony\Call\CallVerifierFactory;
use Eloquent\Phony\Call\Event\ReceivedEvent;
use Eloquent\Phony\Call\Event\ReceivedExceptionEvent;
use Eloquent\Phony\Event\EventCollection;
use Eloquent\Phony\Matcher\Matcher;
use Eloquent\Phony\Matcher\MatcherFactory;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Spy\Spy;
use InvalidArgumentException;
use Throwable;

/**
 * Checks and asserts the behavior of generators.
 */
class GeneratorVerifier extends IterableVerifier
{
    /**
     * Construct a new generator verifier.
     *
     * @param Spy|Call            $subject             The subject.
     * @param array<int,Call>     $calls               The generator calls.
     * @param MatcherFactory      $matcherFactory      The matcher factory to use.
     * @param CallVerifierFactory $callVerifierFactory The call verifier factory to use.
     * @param AssertionRecorder   $assertionRecorder   The assertion recorder to use.
     * @param AssertionRenderer   $assertionRenderer   The assertion renderer to use.
     */
    public function __construct(
        $subject,
        array $calls,
        MatcherFactory $matcherFactory,
        CallVerifierFactory $callVerifierFactory,
        AssertionRecorder $assertionRecorder,
        AssertionRenderer $assertionRenderer
    ) {
        parent::__construct(
            $subject,
            $calls,
            $matcherFactory,
            $callVerifierFactory,
            $assertionRecorder,
            $assertionRenderer
        );

        $this->isGenerator = true;
    }

    /**
     * Checks if the subject received the supplied value.
     *
     * When called with no arguments, this method simply checks that the subject
     * received any value.
     *
     * @param mixed $value The value.
     *
     * @return ?EventCollection The result.
     */
    public function checkReceived($value = null): ?EventCollection
    {
        $cardinality = $this->resetCardinality();
        $argumentCount = func_num_args();

        if (0 === $argumentCount) {
            $checkValue = false;
        } else {
            $checkValue = true;
            $value = $this->matcherFactory->adapt($value);
        }

        $isCall = $this->subject instanceof Call;
        $matchingEvents = [];
        $matchCount = 0;
        $eventCount = 0;

        foreach ($this->calls as $call) {
            $isMatchingCall = false;

            foreach ($call->iterableEvents() as $event) {
                if ($event instanceof ReceivedEvent) {
                    ++$eventCount;

                    if (!$checkValue || $value->matches($event->value())) {
                        $matchingEvents[] = $event;
                        $isMatchingCall = true;

                        if ($isCall) {
                            ++$matchCount;
                        }
                    }
                }
            }

            if (!$isCall && $isMatchingCall) {
                ++$matchCount;
            }
        }

        if ($isCall) {
            $totalCount = $eventCount;
        } else {
            $totalCount = $this->callCount;
        }

        if ($cardinality->matches($matchCount, $totalCount)) {
            return $this->assertionRecorder->createSuccess($matchingEvents);
        }

        return null;
    }

    /**
     * Throws an exception unless the subject received the supplied value.
     *
     * When called with no arguments, this method simply checks that the subject
     * received any value.
     *
     * @param mixed $value The value.
     *
     * @return ?EventCollection The result, or null if the assertion recorder does not throw exceptions.
     * @throws Throwable        If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function received($value = null): ?EventCollection
    {
        $cardinality = $this->cardinality;
        $argumentCount = func_num_args();

        if (0 === $argumentCount) {
            $arguments = [];
        } else {
            $value = $this->matcherFactory->adapt($value);
            $arguments = [$value];
        }

        if ($result = $this->checkReceived(...$arguments)) {
            return $result;
        }

        return $this->assertionRecorder->createFailure(
            $this->assertionRenderer
                ->renderGeneratorReceived($this->subject, $cardinality, $value)
        );
    }

    /**
     * Checks if the subject received an exception of the supplied type.
     *
     * @param Matcher|Throwable|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return ?EventCollection         The result.
     * @throws InvalidArgumentException If the type is invalid.
     */
    public function checkReceivedException($type = null): ?EventCollection
    {
        $cardinality = $this->resetCardinality();

        $isCall = $this->subject instanceof Call;
        $matchingEvents = [];
        $matchCount = 0;
        $eventCount = 0;
        $isTypeSupported = false;

        if (!$type) {
            $isTypeSupported = true;

            foreach ($this->calls as $call) {
                $isMatchingCall = false;

                foreach ($call->iterableEvents() as $event) {
                    if ($event instanceof ReceivedExceptionEvent) {
                        ++$eventCount;

                        $matchingEvents[] = $event;
                        $isMatchingCall = true;

                        if ($isCall) {
                            ++$matchCount;
                        }
                    }
                }

                if (!$isCall && $isMatchingCall) {
                    ++$matchCount;
                }
            }
        } elseif (is_string($type)) {
            $isTypeSupported = true;

            foreach ($this->calls as $call) {
                $isMatchingCall = false;

                foreach ($call->iterableEvents() as $event) {
                    if ($event instanceof ReceivedExceptionEvent) {
                        ++$eventCount;

                        if (is_a($event->exception(), $type)) {
                            $matchingEvents[] = $event;
                            $isMatchingCall = true;

                            if ($isCall) {
                                ++$matchCount;
                            }
                        }
                    }
                }

                if (!$isCall && $isMatchingCall) {
                    ++$matchCount;
                }
            }
        } elseif (is_object($type)) {
            if ($type instanceof InstanceHandle) {
                $type = $type->get();
            }

            if ($type instanceof Throwable) {
                $isTypeSupported = true;
                $type = $this->matcherFactory->equalTo($type, true);
            } elseif ($this->matcherFactory->isMatcher($type)) {
                $isTypeSupported = true;
                $type = $this->matcherFactory->adapt($type);
            }

            if ($isTypeSupported) {
                /** @var Matcher */
                $typeMatcher = $type;

                foreach ($this->calls as $call) {
                    $isMatchingCall = false;

                    foreach ($call->iterableEvents() as $event) {
                        if ($event instanceof ReceivedExceptionEvent) {
                            ++$eventCount;

                            if ($typeMatcher->matches($event->exception())) {
                                $matchingEvents[] = $event;
                                $isMatchingCall = true;

                                if ($isCall) {
                                    ++$matchCount;
                                }
                            }
                        }
                    }

                    if (!$isCall && $isMatchingCall) {
                        ++$matchCount;
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

        if ($isCall) {
            $totalCount = $eventCount;
        } else {
            $totalCount = $this->callCount;
        }

        if ($cardinality->matches($matchCount, $totalCount)) {
            return $this->assertionRecorder->createSuccess($matchingEvents);
        }

        return null;
    }

    /**
     * Throws an exception unless the subject received an exception of the
     * supplied type.
     *
     * @param Matcher|Throwable|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return ?EventCollection         The result, or null if the assertion recorder does not throw exceptions.
     * @throws InvalidArgumentException If the type is invalid.
     * @throws Throwable                If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function receivedException($type = null): ?EventCollection
    {
        $cardinality = $this->cardinality;

        if ($type instanceof InstanceHandle) {
            /** @var Throwable */
            $type = $type->get();
        }

        if ($type instanceof Throwable) {
            $type = $this->matcherFactory->equalTo($type, true);
        } elseif ($this->matcherFactory->isMatcher($type)) {
            $type = $this->matcherFactory->adapt($type);
        }

        if ($result = $this->checkReceivedException($type)) {
            return $result;
        }

        return $this->assertionRecorder->createFailure(
            $this->assertionRenderer->renderGeneratorReceivedException(
                $this->subject,
                $cardinality,
                $type
            )
        );
    }

    /**
     * Checks if the subject returned the supplied value from a generator.
     *
     * @param mixed $value The value.
     *
     * @return ?EventCollection The result.
     */
    public function checkReturned($value = null): ?EventCollection
    {
        $cardinality = $this->resetCardinality();

        if ($this->subject instanceof Call) {
            $cardinality->assertSingular();
        }

        $matchingEvents = [];
        $matchCount = 0;

        if (0 === func_num_args()) {
            foreach ($this->calls as $call) {
                if (!$call->isGenerator() || !$endEvent = $call->endEvent()) {
                    continue;
                }

                list($exception) = $call->generatorResponse();

                if (!$exception) {
                    $matchingEvents[] = $endEvent;
                    ++$matchCount;
                }
            }
        } else {
            $value = $this->matcherFactory->adapt($value);

            foreach ($this->calls as $call) {
                if (!$call->isGenerator() || !$endEvent = $call->endEvent()) {
                    continue;
                }

                list($exception, $returnValue) = $call->generatorResponse();

                if (!$exception && $value->matches($returnValue)) {
                    $matchingEvents[] = $endEvent;
                    ++$matchCount;
                }
            }
        }

        if ($cardinality->matches($matchCount, $this->callCount)) {
            return $this->assertionRecorder->createSuccess($matchingEvents);
        }

        return null;
    }

    /**
     * Throws an exception unless the subject returned the supplied value from a
     * generator.
     *
     * @param mixed $value The value.
     *
     * @return ?EventCollection The result, or null if the assertion recorder does not throw exceptions.
     * @throws Throwable        If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function returned($value = null): ?EventCollection
    {
        $cardinality = $this->cardinality;
        $argumentCount = func_num_args();

        if (0 === $argumentCount) {
            $arguments = [];
        } else {
            $value = $this->matcherFactory->adapt($value);
            $arguments = [$value];
        }

        if ($result = $this->checkReturned(...$arguments)) {
            return $result;
        }

        return $this->assertionRecorder->createFailure(
            $this->assertionRenderer
                ->renderGeneratorReturned($this->subject, $cardinality, $value)
        );
    }

    /**
     * Checks if an exception of the supplied type was thrown from a generator.
     *
     * @param Matcher|Throwable|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return ?EventCollection         The result.
     * @throws InvalidArgumentException If the type is invalid.
     */
    public function checkThrew($type = null): ?EventCollection
    {
        $cardinality = $this->resetCardinality();

        if ($this->subject instanceof Call) {
            $cardinality->assertSingular();
        }

        $matchingEvents = [];
        $matchCount = 0;
        $isTypeSupported = false;

        if (!$type) {
            $isTypeSupported = true;

            foreach ($this->calls as $call) {
                if (!$call->isGenerator() || !$endEvent = $call->endEvent()) {
                    continue;
                }

                list($exception) = $call->generatorResponse();

                if ($exception) {
                    $matchingEvents[] = $endEvent;
                    ++$matchCount;
                }
            }
        } elseif (is_string($type)) {
            $isTypeSupported = true;

            foreach ($this->calls as $call) {
                if (!$call->isGenerator() || !$endEvent = $call->endEvent()) {
                    continue;
                }

                list($exception) = $call->generatorResponse();

                if ($exception && is_a($exception, $type)) {
                    $matchingEvents[] = $endEvent;
                    ++$matchCount;
                }
            }
        } elseif (is_object($type)) {
            if ($type instanceof InstanceHandle) {
                $type = $type->get();
            }

            if ($type instanceof Throwable) {
                $isTypeSupported = true;
                $type = $this->matcherFactory->equalTo($type, true);
            } elseif ($this->matcherFactory->isMatcher($type)) {
                $isTypeSupported = true;
                $type = $this->matcherFactory->adapt($type);
            }

            if ($isTypeSupported) {
                /** @var Matcher */
                $typeMatcher = $type;

                foreach ($this->calls as $call) {
                    if (
                        !$call->isGenerator() ||
                        !$endEvent = $call->endEvent()
                    ) {
                        continue;
                    }

                    list($exception) = $call->generatorResponse();

                    if ($exception && $typeMatcher->matches($exception)) {
                        $matchingEvents[] = $endEvent;
                        ++$matchCount;
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

        if ($cardinality->matches($matchCount, $this->callCount)) {
            return $this->assertionRecorder->createSuccess($matchingEvents);
        }

        return null;
    }

    /**
     * Throws an exception unless an exception of the supplied type was thrown
     * from a generator.
     *
     * @param Matcher|Throwable|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return ?EventCollection         The result, or null if the assertion recorder does not throw exceptions.
     * @throws InvalidArgumentException If the type is invalid.
     * @throws Throwable                If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function threw($type = null): ?EventCollection
    {
        $cardinality = $this->cardinality;

        if ($type instanceof InstanceHandle) {
            /** @var Throwable */
            $type = $type->get();
        }

        if ($type instanceof Throwable) {
            $type = $this->matcherFactory->equalTo($type, true);
        } elseif ($this->matcherFactory->isMatcher($type)) {
            $type = $this->matcherFactory->adapt($type);
        }

        if ($result = $this->checkThrew($type)) {
            return $result;
        }

        return $this->assertionRecorder->createFailure(
            $this->assertionRenderer
                ->renderGeneratorThrew($this->subject, $cardinality, $type)
        );
    }
}
