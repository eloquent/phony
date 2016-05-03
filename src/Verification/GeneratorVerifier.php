<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Verification;

use Eloquent\Phony\Call\Event\ReceivedEvent;
use Eloquent\Phony\Call\Event\ReceivedExceptionEvent;
use Eloquent\Phony\Event\EventCollection;
use Error;
use Exception;
use InvalidArgumentException;
use Throwable;

/**
 * Checks and asserts the behavior of generators.
 */
class GeneratorVerifier extends TraversableVerifier
{
    /**
     * Checks if the subject received the supplied value.
     *
     * When called with no arguments, this method simply checks that the subject
     * received any value.
     *
     * @param mixed $value The value.
     *
     * @return EventCollection|null The result.
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

        foreach ($this->calls as $call) {
            $callMatched = false;

            foreach ($call->traversableEvents() as $event) {
                if ($event instanceof ReceivedEvent) {
                    if (!$checkValue || $value->matches($event->value())) {
                        $matchingEvents[] = $event;
                        $callMatched = true;
                    }
                }
            }

            if ($callMatched) {
                ++$matchCount;
            }
        }

        if ($cardinality->matches($matchCount, $this->callCount)) {
            return $this->assertionRecorder->createSuccess($matchingEvents);
        }
    }

    /**
     * Throws an exception unless the subject received the supplied value.
     *
     * When called with no arguments, this method simply checks that the subject
     * received any value.
     *
     * @param mixed $value The value.
     *
     * @return EventCollection The result.
     * @throws Exception       If the assertion fails, and the assertion recorder throws exceptions.
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

        $renderedSubject =
            $this->assertionRenderer->renderCallable($this->subject);

        if (0 === $argumentCount) {
            $renderedType = sprintf(
                'generator returned by %s to receive value',
                $renderedSubject
            );
        } else {
            $renderedType = sprintf(
                'generator returned by %s to receive value like %s',
                $renderedSubject,
                $value->describe()
            );
        }

        if ($this->callCount) {
            $renderedActual = sprintf(
                "Responded:\n%s",
                $this->assertionRenderer->renderResponses($this->calls, true)
            );
        } else {
            $renderedActual = 'Never called.';
        }

        return $this->assertionRecorder->createFailure(
            sprintf(
                'Expected %s. %s',
                $this->assertionRenderer
                    ->renderCardinality($cardinality, $renderedType),
                $renderedActual
            )
        );
    }

    /**
     * Checks if the subject received an exception of the supplied type.
     *
     * @param Exception|Error|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return EventCollection|null     The result.
     * @throws InvalidArgumentException If the type is invalid.
     */
    public function checkReceivedException($type = null)
    {
        $cardinality = $this->resetCardinality();

        $matchingEvents = array();
        $matchCount = 0;
        $isTypeSupported = false;

        if (!$type) {
            $isTypeSupported = true;

            foreach ($this->calls as $call) {
                foreach ($call->traversableEvents() as $event) {
                    if ($event instanceof ReceivedExceptionEvent) {
                        $matchingEvents[] = $event;
                        ++$matchCount;
                    }
                }
            }
        } elseif (is_string($type)) {
            $isTypeSupported = true;

            foreach ($this->calls as $call) {
                foreach ($call->traversableEvents() as $event) {
                    if ($event instanceof ReceivedExceptionEvent) {
                        if (is_a($event->exception(), $type)) {
                            $matchingEvents[] = $event;
                            ++$matchCount;
                        }
                    }
                }
            }
        } elseif (is_object($type)) {
            if ($type instanceof Throwable || $type instanceof Exception) {
                $isTypeSupported = true;

                foreach ($this->calls as $call) {
                    foreach ($call->traversableEvents() as $event) {
                        if ($event instanceof ReceivedExceptionEvent) {
                            if ($event->exception() == $type) {
                                $matchingEvents[] = $event;
                                ++$matchCount;
                            }
                        }
                    }
                }
            } elseif ($this->matcherFactory->isMatcher($type)) {
                $isTypeSupported = true;
                $type = $this->matcherFactory->adapt($type);

                foreach ($this->calls as $call) {
                    foreach ($call->traversableEvents() as $event) {
                        if ($event instanceof ReceivedExceptionEvent) {
                            if ($type->matches($event->exception())) {
                                $matchingEvents[] = $event;
                                ++$matchCount;
                            }
                        }
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
    }

    /**
     * Throws an exception unless the subject received an exception of the
     * supplied type.
     *
     * @param Exception|Error|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return EventCollection          The result.
     * @throws InvalidArgumentException If the type is invalid.
     * @throws Exception                If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function receivedException($type = null)
    {
        $cardinality = $this->cardinality;

        if ($result = $this->checkReceivedException($type)) {
            return $result;
        }

        $renderedSubject =
            $this->assertionRenderer->renderCallable($this->subject);

        if (!$type) {
            $renderedType = sprintf(
                'generator returned by %s to receive exception',
                $renderedSubject
            );
        } elseif (is_string($type)) {
            $renderedType = sprintf(
                'generator returned by %s to receive %s exception',
                $renderedSubject,
                $type
            );
        } elseif (is_object($type)) {
            if ($type instanceof Throwable || $type instanceof Exception) {
                $renderedType = sprintf(
                    'generator returned by %s to receive exception equal to %s',
                    $renderedSubject,
                    $this->assertionRenderer->renderException($type)
                );
            } elseif ($this->matcherFactory->isMatcher($type)) {
                $renderedType = sprintf(
                    'generator returned by %s to receive exception like %s',
                    $renderedSubject,
                    $this->matcherFactory->adapt($type)->describe()
                );
            }
        }

        if ($this->callCount) {
            $renderedActual = sprintf(
                "Responded:\n%s",
                $this->assertionRenderer->renderResponses($this->calls, true)
            );
        } else {
            $renderedActual = 'Never called.';
        }

        return $this->assertionRecorder->createFailure(
            sprintf(
                'Expected %s. %s',
                $this->assertionRenderer
                    ->renderCardinality($cardinality, $renderedType),
                $renderedActual
            )
        );
    }

    /**
     * Checks if the subject returned the supplied value from a generator.
     *
     * @param mixed $value The value.
     *
     * @return EventCollection|null The result.
     */
    public function checkReturned($value = null)
    {
        $cardinality = $this->resetCardinality();

        $matchingEvents = array();
        $matchCount = 0;

        if (0 === func_num_args()) {
            foreach ($this->calls as $call) {
                if (!$call->isGenerator() || !$endEvent = $call->endEvent()) {
                    continue;
                }

                list($exception, $returnValue) = $call->generatorResponse();

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
    }

    /**
     * Throws an exception unless the subject returned the supplied value from a
     * generator.
     *
     * @param mixed $value The value.
     *
     * @return EventCollection The result.
     * @throws Exception       If the assertion fails, and the assertion recorder throws exceptions.
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

        $renderedSubject =
            $this->assertionRenderer->renderCallable($this->subject);

        if (0 === $argumentCount) {
            $renderedType =
                sprintf('call on %s to return via generator', $renderedSubject);
        } else {
            $renderedType = sprintf(
                'call on %s to return like %s via generator',
                $renderedSubject,
                $value->describe()
            );
        }

        if ($this->callCount) {
            $renderedActual = sprintf(
                "Responded:\n%s",
                $this->assertionRenderer->renderResponses($this->calls, true)
            );
        } else {
            $renderedActual = 'Never called.';
        }

        return $this->assertionRecorder->createFailure(
            sprintf(
                'Expected %s. %s',
                $this->assertionRenderer
                    ->renderCardinality($cardinality, $renderedType),
                $renderedActual
            )
        );
    }

    /**
     * Checks if an exception of the supplied type was thrown from a generator.
     *
     * @param Exception|Error|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return EventCollection|null     The result.
     * @throws InvalidArgumentException If the type is invalid.
     */
    public function checkThrew($type = null)
    {
        $cardinality = $this->resetCardinality();

        $matchingEvents = array();
        $matchCount = 0;
        $isTypeSupported = false;

        if (!$type) {
            $isTypeSupported = true;

            foreach ($this->calls as $call) {
                if (!$call->isGenerator() || !$endEvent = $call->endEvent()) {
                    continue;
                }

                list($exception, $returnValue) = $call->generatorResponse();

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

                list($exception, $returnValue) = $call->generatorResponse();

                if ($exception && is_a($exception, $type)) {
                    $matchingEvents[] = $endEvent;
                    ++$matchCount;
                }
            }
        } elseif (is_object($type)) {
            if ($type instanceof Throwable || $type instanceof Exception) {
                $isTypeSupported = true;

                foreach ($this->calls as $call) {
                    if (
                        !$call->isGenerator() ||
                        !$endEvent = $call->endEvent()
                    ) {
                        continue;
                    }

                    list($exception, $returnValue) = $call->generatorResponse();

                    if ($exception == $type) {
                        $matchingEvents[] = $endEvent;
                        ++$matchCount;
                    }
                }
            } elseif ($this->matcherFactory->isMatcher($type)) {
                $isTypeSupported = true;
                $type = $this->matcherFactory->adapt($type);

                foreach ($this->calls as $call) {
                    if (
                        !$call->isGenerator() ||
                        !$endEvent = $call->endEvent()
                    ) {
                        continue;
                    }

                    list($exception, $returnValue) = $call->generatorResponse();

                    if ($exception && $type->matches($exception)) {
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
    }

    /**
     * Throws an exception unless an exception of the supplied type was thrown
     * from a generator.
     *
     * @param Exception|Error|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return EventCollection          The result.
     * @throws InvalidArgumentException If the type is invalid.
     * @throws Exception                If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function threw($type = null)
    {
        $cardinality = $this->cardinality;

        if ($result = $this->checkThrew($type)) {
            return $result;
        }

        $renderedSubject =
            $this->assertionRenderer->renderCallable($this->subject);

        if (!$type) {
            $renderedType =
                sprintf('call on %s to throw via generator', $renderedSubject);
        } elseif (is_string($type)) {
            $renderedType = sprintf(
                'call on %s to throw %s exception via generator',
                $renderedSubject,
                $type
            );
        } elseif (is_object($type)) {
            if ($type instanceof Throwable || $type instanceof Exception) {
                $renderedType = sprintf(
                    'call on %s to throw exception equal to %s via generator',
                    $renderedSubject,
                    $this->assertionRenderer->renderException($type)
                );
            } elseif ($this->matcherFactory->isMatcher($type)) {
                $renderedType = sprintf(
                    'call on %s to throw exception like %s via generator',
                    $renderedSubject,
                    $this->matcherFactory->adapt($type)->describe()
                );
            }
        }

        if ($this->callCount) {
            $renderedActual = sprintf(
                "Responded:\n%s",
                $this->assertionRenderer->renderResponses($this->calls, true)
            );
        } else {
            $renderedActual = 'Never called.';
        }

        return $this->assertionRecorder->createFailure(
            sprintf(
                'Expected %s. %s',
                $this->assertionRenderer
                    ->renderCardinality($cardinality, $renderedType),
                $renderedActual
            )
        );
    }
}
