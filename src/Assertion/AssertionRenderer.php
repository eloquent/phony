<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Assertion;

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Call\Call;
use Eloquent\Phony\Call\Event\CalledEvent;
use Eloquent\Phony\Call\Event\CallEvent;
use Eloquent\Phony\Call\Event\ConsumedEvent;
use Eloquent\Phony\Call\Event\ProducedEvent;
use Eloquent\Phony\Call\Event\ReceivedEvent;
use Eloquent\Phony\Call\Event\ReceivedExceptionEvent;
use Eloquent\Phony\Call\Event\ReturnedEvent;
use Eloquent\Phony\Call\Event\ThrewEvent;
use Eloquent\Phony\Cardinality\Cardinality;
use Eloquent\Phony\Event\EventCollection;
use Eloquent\Phony\Event\NullEvent;
use Eloquent\Phony\Exporter\Exporter;
use Eloquent\Phony\Exporter\InlineExporter;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\WrappedInvocable;
use Eloquent\Phony\Matcher\Matcher;
use Eloquent\Phony\Mock\Handle\Handle;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Mock\Method\WrappedMethod;
use Eloquent\Phony\Spy\Spy;
use Eloquent\Phony\Stub\Stub;
use Error;
use Exception;
use ReflectionException;
use ReflectionMethod;

/**
 * Renders various data for use in assertion messages.
 */
class AssertionRenderer
{
    /**
     * Get the static instance of this renderer.
     *
     * @return AssertionRenderer The static renderer.
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self(
                InvocableInspector::instance(),
                InlineExporter::instance()
            );
        }

        return self::$instance;
    }

    /**
     * Construct a new call renderer.
     *
     * @param InvocableInspector $invocableInspector The invocable inspector to use.
     * @param Exporter           $exporter           The exporter to use.
     */
    public function __construct(
        InvocableInspector $invocableInspector,
        Exporter $exporter
    ) {
        $this->invocableInspector = $invocableInspector;
        $this->exporter = $exporter;
    }

    /**
     * Render a value.
     *
     * @param mixed $value The value.
     *
     * @return string The rendered value.
     */
    public function renderValue($value)
    {
        return $this->exporter->export($value);
    }

    /**
     * Render a mock.
     *
     * @param Handle $handle The handle.
     *
     * @return string The rendered mock.
     */
    public function renderMock(Handle $handle)
    {
        $class = $handle->clazz();

        if ($parentClass = $class->getParentClass()) {
            $class = $parentClass;
        }

        $atoms = explode('\\', $class->getName());
        $rendered = array_pop($atoms);

        if ($handle instanceof InstanceHandle) {
            $label = $handle->label();

            if (null !== $label) {
                $rendered .= '[' . $label . ']';
            }
        } else {
            $rendered .= '[static]';
        }

        return $rendered;
    }

    /**
     * Render a callable.
     *
     * @param callable $callback The callable.
     *
     * @return string The rendered callable.
     */
    public function renderCallable($callback)
    {
        $wrappedCallback = null;

        while ($callback instanceof WrappedInvocable) {
            $wrappedCallback = $callback;
            $callback = $callback->callback();
        }

        $rendered = null;
        $label = null;

        if ($wrappedCallback) {
            if ($wrappedCallback->isAnonymous()) {
                if ($wrappedCallback instanceof Spy) {
                    $rendered = '{spy}';
                } elseif ($wrappedCallback instanceof Stub) {
                    $rendered = '{stub}';
                }
            }

            $label = $wrappedCallback->label();
        }

        if (!$rendered) {
            $reflector = $this->invocableInspector
                ->callbackReflector($callback);

            if ($reflector instanceof ReflectionMethod) {
                $class = $reflector->getDeclaringClass();

                if (
                    $class->implementsInterface(
                        'Eloquent\Phony\Mock\Mock'
                    )
                ) {
                    if ($parentClass = $class->getParentClass()) {
                        $class = $parentClass;
                    } else {
                        try {
                            $prototype = $reflector->getPrototype();
                            $class = $prototype->getDeclaringClass();
                        } catch (ReflectionException $e) {
                            // ignore
                        }
                    }
                }

                $atoms = explode('\\', $class->getName());
                $rendered = array_pop($atoms);

                if ($wrappedCallback instanceof WrappedMethod) {
                    $name = $wrappedCallback->name();
                    $handle = $wrappedCallback->handle();

                    if ($handle instanceof InstanceHandle) {
                        $mockLabel = $handle->label();

                        if (null !== $mockLabel) {
                            $rendered .= '[' . $mockLabel . ']';
                        }
                    }
                } else {
                    $name = $reflector->getName();
                }

                if ($reflector->isStatic()) {
                    $callOperator = '::';
                } else {
                    $callOperator = '->';
                }

                $rendered .= $callOperator . $name;
            } else {
                $rendered = $reflector->getName();
            }
        }

        if (null !== $label) {
            $rendered .= sprintf('[%s]', $label);
        }

        return $rendered;
    }

    /**
     * Render a sequence of matchers.
     *
     * @param array<Matcher> $matchers The matchers.
     *
     * @return string The rendered matchers.
     */
    public function renderMatchers(array $matchers)
    {
        if (count($matchers) < 1) {
            return '<none>';
        }

        $rendered = array();
        foreach ($matchers as $matcher) {
            $rendered[] = $matcher->describe($this->exporter);
        }

        return implode(', ', $rendered);
    }

    /**
     * Render a cardinality.
     *
     * @param Cardinality $cardinality The cardinality.
     * @param string      $subject     The subject.
     *
     * @return string The rendered cardinality.
     */
    public function renderCardinality(
        Cardinality $cardinality,
        $subject
    ) {
        if ($cardinality->isNever()) {
            return sprintf('no %s', $subject);
        }

        $isAlways = $cardinality->isAlways();

        if ($isAlways) {
            $rendered = sprintf('every %s', $subject);
        } else {
            $rendered = $subject;
        }

        $minimum = $cardinality->minimum();
        $maximum = $cardinality->maximum();

        if (!$minimum) {
            if (null === $maximum) {
                return $rendered . ', any number of times';
            }

            if (1 === $maximum) {
                return $rendered . ', up to 1 time';
            }

            return $rendered . sprintf(', up to %d times', $maximum);
        }

        if (null === $maximum) {
            if (1 === $minimum) {
                return $rendered;
            }

            return $rendered . sprintf(', %d times', $minimum);
        }

        if ($minimum === $maximum) {
            if (1 === $minimum) {
                return $rendered . ', exactly 1 time';
            }

            return $rendered . sprintf(', exactly %d times', $minimum);
        }

        return $rendered .
            sprintf(', between %d and %d times', $minimum, $maximum);
    }

    /**
     * Render a sequence of calls.
     *
     * @param array<Call> $calls The calls.
     *
     * @return string The rendered calls.
     */
    public function renderCalls(array $calls)
    {
        usort(
            $calls,
            function ($left, $right) {
                return $left->sequenceNumber() > $right->sequenceNumber();
            }
        );

        $rendered = array();

        foreach ($calls as $call) {
            $rendered[] = sprintf('    - %s', $this->renderCall($call));
        }

        return implode("\n", $rendered);
    }

    /**
     * Render the $this values of a sequence of calls.
     *
     * @param array<Call> $calls The calls.
     *
     * @return string The rendered call $this values.
     */
    public function renderThisValues(array $calls)
    {
        $rendered = array();
        foreach ($calls as $call) {
            $rendered[] = sprintf(
                '    - %s',
                $this->renderValue(
                    $this->invocableInspector
                        ->callbackThisValue($call->callback())
                )
            );
        }

        return implode("\n", $rendered);
    }

    /**
     * Render the arguments of a sequence of calls.
     *
     * @param array<Call> $calls The calls.
     *
     * @return string The rendered call arguments.
     */
    public function renderCallsArguments(array $calls)
    {
        $rendered = array();
        foreach ($calls as $call) {
            $rendered[] =
                sprintf('    - %s', $this->renderArguments($call->arguments()));
        }

        return implode("\n", $rendered);
    }

    /**
     * Render the responses of a sequence of calls.
     *
     * @param array<Call> $calls              The calls.
     * @param bool        $expandTraversables True if traversable events should be rendered.
     *
     * @return string The rendered call responses.
     */
    public function renderResponses(array $calls, $expandTraversables = false)
    {
        $rendered = array();

        foreach ($calls as $call) {
            if (!$call->hasResponded()) {
                $rendered[] = '    - <none>';

                continue;
            }

            list($exception, $returnValue) = $call->response();

            if ($exception) {
                $rendered[] = sprintf(
                    '    - threw %s',
                    $this->renderException($exception)
                );
            } elseif ($expandTraversables && $call->isTraversable()) {
                if ($call->isGenerator()) {
                    $rendered[] = sprintf(
                        "    - generated:\n%s",
                        $this->indent($this->renderProduced($call))
                    );
                } else {
                    $rendered[] = sprintf(
                        "    - returned %s producing:\n%s",
                        $this->exporter->export($returnValue, 0),
                        $this->indent($this->renderProduced($call))
                    );
                }
            } else {
                $rendered[] = sprintf(
                    '    - returned %s',
                    $this->renderValue($returnValue)
                );
            }
        }

        return implode("\n", $rendered);
    }

    /**
     * Render the supplied call.
     *
     * @param Call $call The call.
     *
     * @return string The rendered call.
     */
    public function renderCall(Call $call)
    {
        return $this->renderCalledEvent($call->calledEvent());
    }

    /**
     * Render the supplied 'called' event.
     *
     * @param CalledEvent $event The 'called' event.
     *
     * @return string The rendered event.
     */
    public function renderCalledEvent(CalledEvent $event)
    {
        $renderedArguments = array();

        foreach ($event->arguments() as $argument) {
            $renderedArguments[] = $this->renderValue($argument);
        }

        return sprintf(
            '%s(%s)',
            $this->renderCallable($event->callback()),
            implode(', ', $renderedArguments)
        );
    }

    /**
     * Render the supplied call's response.
     *
     * @param Call $call The call.
     *
     * @return string The rendered response.
     */
    public function renderResponse(Call $call)
    {
        $responseEvent = $call->responseEvent();

        if ($responseEvent instanceof ReturnedEvent) {
            return sprintf(
                'Returned %s.',
                $this->renderValue($responseEvent->value())
            );
        }

        if ($responseEvent instanceof ThrewEvent) {
            return sprintf(
                'Threw %s.',
                $this->renderException($responseEvent->exception())
            );
        }

        return 'Never responded.';
    }

    /**
     * Render the traversable events of a call.
     *
     * @param Call $call The call.
     *
     * @return string The rendered traversable events.
     */
    public function renderProduced(Call $call)
    {
        $rendered = array();

        foreach ($call->traversableEvents() as $event) {
            if ($event instanceof ProducedEvent) {
                $rendered[] = sprintf(
                    '    - produced %s: %s',
                    $this->renderValue($event->key()),
                    $this->renderValue($event->value())
                );
            } elseif ($event instanceof ReceivedEvent) {
                $rendered[] = sprintf(
                    '    - received %s',
                    $this->renderValue($event->value())
                );
            } elseif ($event instanceof ReceivedExceptionEvent) {
                $rendered[] = sprintf(
                    '    - received exception %s',
                    $this->renderException($event->exception())
                );
            }
        }

        if ($call->endEvent()) {
            $rendered[] = '    - finished iterating';
        } else {
            $rendered[] = '    - did not finish iterating';
        }

        return implode("\n", $rendered);
    }

    /**
     * Render a sequence of arguments.
     *
     * @param Arguments $arguments The arguments.
     *
     * @return string The rendered arguments.
     */
    public function renderArguments(Arguments $arguments)
    {
        if (count($arguments) < 1) {
            return '<none>';
        }

        $rendered = array();

        foreach ($arguments as $argument) {
            $rendered[] = $this->renderValue($argument);
        }

        return implode(', ', $rendered);
    }

    /**
     * Render an exception.
     *
     * @param Exception|Error $exception The exception.
     *
     * @return string The rendered exception.
     */
    public function renderException($exception)
    {
        if ('' === $exception->getMessage()) {
            $renderedMessage = '';
        } else {
            $message = $exception->getMessage();
            $renderedMessage = $this->exporter->export($message, 0);
        }

        $atoms = explode('\\', get_class($exception));
        $class = array_pop($atoms);

        return sprintf('%s(%s)', $class, $renderedMessage);
    }

    /**
     * Render an arbitrary sequence of events.
     *
     * @param EventCollection $events The events.
     *
     * @return string The rendered events.
     */
    public function renderEvents(EventCollection $events)
    {
        $rendered = array();

        foreach ($events->allEvents() as $event) {
            if ($event instanceof CallEvent) {
                if ($call = $event->call()) {
                    $call = $this->renderCall($call);
                } else {
                    $call = 'unknown call';
                }
            }

            if ($event instanceof Call) {
                $rendered[] =
                    sprintf('    - called %s', $this->renderCall($event));
            } elseif ($event instanceof CalledEvent) {
                $rendered[] = sprintf(
                    '    - called %s',
                    $this->renderCalledEvent($event)
                );
            } elseif ($event instanceof ReturnedEvent) {
                $rendered[] = sprintf(
                    '    - returned %s from %s',
                    $this->renderValue($event->value()),
                    $call
                );
            } elseif ($event instanceof ThrewEvent) {
                $rendered[] = sprintf(
                    '    - threw %s in %s',
                    $this->renderException($event->exception()),
                    $call
                );
            } elseif ($event instanceof ProducedEvent) {
                $rendered[] = sprintf(
                    '    - produced %s: %s from %s',
                    $this->renderValue($event->key()),
                    $this->renderValue($event->value()),
                    $call
                );
            } elseif ($event instanceof ReceivedEvent) {
                $rendered[] = sprintf(
                    '    - received %s in %s',
                    $this->renderValue($event->value()),
                    $call
                );
            } elseif ($event instanceof ReceivedExceptionEvent) {
                $rendered[] = sprintf(
                    '    - received exception %s in %s',
                    $this->renderException($event->exception()),
                    $call
                );
            } elseif ($event instanceof ConsumedEvent) {
                $rendered[] = sprintf('    - %s finished iterating', $call);
            } elseif ($event instanceof NullEvent) {
                $rendered[] = '    - <none>';
            } else {
                $rendered[] = sprintf(
                    '    - %s event',
                    $this->renderValue(get_class($event))
                );
            }
        }

        return implode("\n", $rendered);
    }

    /**
     * Indent the supplied string.
     *
     * @param string $string The string to indent.
     *
     * @return string The indented string.
     */
    protected function indent($string)
    {
        $lines = preg_split('/\R/', $string);

        return '    ' . implode("\n    ", $lines);
    }

    private static $instance;
    private $invocableInspector;
    private $exporter;
}
