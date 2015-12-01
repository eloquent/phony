<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Assertion\Renderer;

use Eloquent\Phony\Call\Argument\Arguments;
use Eloquent\Phony\Call\Argument\ArgumentsInterface;
use Eloquent\Phony\Call\CallInterface;
use Eloquent\Phony\Call\Event\CalledEventInterface;
use Eloquent\Phony\Call\Event\CallEventInterface;
use Eloquent\Phony\Call\Event\ConsumedEventInterface;
use Eloquent\Phony\Call\Event\ProducedEventInterface;
use Eloquent\Phony\Call\Event\ReceivedEventInterface;
use Eloquent\Phony\Call\Event\ReceivedExceptionEventInterface;
use Eloquent\Phony\Call\Event\ReturnedEventInterface;
use Eloquent\Phony\Call\Event\ThrewEventInterface;
use Eloquent\Phony\Cardinality\CardinalityInterface;
use Eloquent\Phony\Event\EventCollectionInterface;
use Eloquent\Phony\Event\NullEventInterface;
use Eloquent\Phony\Exporter\ExporterInterface;
use Eloquent\Phony\Exporter\InlineExporter;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\InvocableInspectorInterface;
use Eloquent\Phony\Invocation\WrappedInvocableInterface;
use Eloquent\Phony\Matcher\MatcherInterface;
use Eloquent\Phony\Mock\Method\WrappedMethodInterface;
use Eloquent\Phony\Mock\Proxy\InstanceProxyInterface;
use Eloquent\Phony\Mock\Proxy\ProxyInterface;
use Eloquent\Phony\Spy\SpyInterface;
use Eloquent\Phony\Stub\StubInterface;
use Error;
use Exception;
use ReflectionException;
use ReflectionMethod;

/**
 * Renders various data for use in assertion messages.
 */
class AssertionRenderer implements AssertionRendererInterface
{
    /**
     * Get the static instance of this renderer.
     *
     * @return AssertionRendererInterface The static renderer.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Construct a new call renderer.
     *
     * @param InvocableInspectorInterface|null $invocableInspector The invocable inspector to use.
     * @param ExporterInterface|null           $exporter           The exporter to use.
     */
    public function __construct(
        InvocableInspectorInterface $invocableInspector = null,
        ExporterInterface $exporter = null
    ) {
        if (null === $invocableInspector) {
            $invocableInspector = InvocableInspector::instance();
        }
        if (null === $exporter) {
            $exporter = InlineExporter::instance();
        }

        $this->invocableInspector = $invocableInspector;
        $this->exporter = $exporter;
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
     * Get the exporter.
     *
     * @return Exporter The exporter.
     */
    public function exporter()
    {
        return $this->exporter;
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
     * @param ProxyInterface $proxy The proxy.
     *
     * @return string The rendered mock.
     */
    public function renderMock(ProxyInterface $proxy)
    {
        $class = $proxy->clazz();

        if ($parentClass = $class->getParentClass()) {
            $class = $parentClass;
        }

        $atoms = explode('\\', $class->getName());
        $rendered = array_pop($atoms);

        if ($proxy instanceof InstanceProxyInterface) {
            $label = $proxy->label();

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

        while ($callback instanceof WrappedInvocableInterface) {
            $wrappedCallback = $callback;
            $callback = $callback->callback();
        }

        $rendered = null;
        $label = null;

        if ($wrappedCallback) {
            if ($wrappedCallback->isAnonymous()) {
                if ($wrappedCallback instanceof SpyInterface) {
                    $rendered = '{spy}';
                } elseif ($wrappedCallback instanceof StubInterface) {
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
                        'Eloquent\Phony\Mock\MockInterface'
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

                if ($wrappedCallback instanceof WrappedMethodInterface) {
                    $name = $wrappedCallback->name();
                    $proxy = $wrappedCallback->proxy();

                    if ($proxy instanceof InstanceProxyInterface) {
                        $mockLabel = $proxy->label();

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
     * @param array<MatcherInterface> $matchers The matchers.
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
            $rendered[] = $matcher->describe();
        }

        return implode(', ', $rendered);
    }

    /**
     * Render a cardinality.
     *
     * @param CardinalityInterface $cardinality The cardinality.
     * @param string               $subject     The subject.
     *
     * @return string The rendered cardinality.
     */
    public function renderCardinality(
        CardinalityInterface $cardinality,
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
     * @param array<CallInterface> $calls The calls.
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
     * @param array<CallInterface> $calls The calls.
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
     * @param array<CallInterface> $calls The calls.
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
     * @param array<CallInterface> $calls              The calls.
     * @param boolean|null         $expandTraversables True if traversable events should be rendered.
     *
     * @return string The rendered call responses.
     */
    public function renderResponses(array $calls, $expandTraversables = null)
    {
        if (null === $expandTraversables) {
            $expandTraversables = false;
        }

        $rendered = array();
        foreach ($calls as $call) {
            if (!$call->hasResponded()) {
                $rendered[] = '    - <none>';
            } elseif ($exception = $call->exception()) {
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
                    $returnValue = $call->returnValue();
                    $rendered[] = sprintf(
                        "    - returned %s producing:\n%s",
                        $this->exporter->export($returnValue, 0),
                        $this->indent($this->renderProduced($call))
                    );
                }
            } else {
                $rendered[] = sprintf(
                    '    - returned %s',
                    $this->renderValue($call->returnValue())
                );
            }
        }

        return implode("\n", $rendered);
    }

    /**
     * Render the supplied call.
     *
     * @param CallInterface $call The call.
     *
     * @return string The rendered call.
     */
    public function renderCall(CallInterface $call)
    {
        return $this->renderCalledEvent($call->calledEvent());
    }

    /**
     * Render the supplied 'called' event.
     *
     * @param CalledEventInterface $event The 'called' event.
     *
     * @return string The rendered event.
     */
    public function renderCalledEvent(CalledEventInterface $event)
    {
        $arguments = $event->arguments();

        $renderedArguments = array();
        foreach ($arguments as $argument) {
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
     * @param CallInterface $call The call.
     *
     * @return string The rendered response.
     */
    public function renderResponse(CallInterface $call)
    {
        $responseEvent = $call->responseEvent();

        if ($responseEvent instanceof ReturnedEventInterface) {
            return sprintf(
                'Returned %s.',
                $this->renderValue($responseEvent->value())
            );
        }

        if ($responseEvent instanceof ThrewEventInterface) {
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
     * @param CallInterface $call The call.
     *
     * @return string The rendered traversable events.
     */
    public function renderProduced(CallInterface $call)
    {
        $rendered = array();

        foreach ($call->traversableEvents() as $event) {
            if ($event instanceof ProducedEventInterface) {
                $rendered[] = sprintf(
                    '    - produced %s: %s',
                    $this->renderValue($event->key()),
                    $this->renderValue($event->value())
                );
            } elseif ($event instanceof ReceivedEventInterface) {
                $rendered[] = sprintf(
                    '    - received %s',
                    $this->renderValue($event->value())
                );
            } elseif ($event instanceof ReceivedExceptionEventInterface) {
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
     * @param ArgumentsInterface|array|null $arguments The arguments.
     *
     * @return string The rendered arguments.
     */
    public function renderArguments($arguments)
    {
        $arguments = Arguments::adapt($arguments);

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
     * @param Exception|Error|null The exception.
     *
     * @return string The rendered exception.
     */
    public function renderException($exception = null)
    {
        if (null === $exception) {
            return '<none>';
        }

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
     * @param EventCollectionInterface $events The events.
     *
     * @return string The rendered events.
     */
    public function renderEvents(EventCollectionInterface $events)
    {
        $rendered = array();

        foreach ($events->allEvents() as $event) {
            if ($event instanceof CallEventInterface) {
                if ($call = $event->call()) {
                    $call = $this->renderCall($call);
                } else {
                    $call = 'unknown call';
                }
            }

            if ($event instanceof CallInterface) {
                $rendered[] =
                    sprintf('    - called %s', $this->renderCall($event));
            } elseif ($event instanceof CalledEventInterface) {
                $rendered[] = sprintf(
                    '    - called %s',
                    $this->renderCalledEvent($event)
                );
            } elseif ($event instanceof ReturnedEventInterface) {
                $rendered[] = sprintf(
                    '    - returned %s from %s',
                    $this->renderValue($event->value()),
                    $call
                );
            } elseif ($event instanceof ThrewEventInterface) {
                $rendered[] = sprintf(
                    '    - threw %s in %s',
                    $this->renderException($event->exception()),
                    $call
                );
            } elseif ($event instanceof ProducedEventInterface) {
                $rendered[] = sprintf(
                    '    - produced %s: %s from %s',
                    $this->renderValue($event->key()),
                    $this->renderValue($event->value()),
                    $call
                );
            } elseif ($event instanceof ReceivedEventInterface) {
                $rendered[] = sprintf(
                    '    - received %s in %s',
                    $this->renderValue($event->value()),
                    $call
                );
            } elseif ($event instanceof ReceivedExceptionEventInterface) {
                $rendered[] = sprintf(
                    '    - received exception %s in %s',
                    $this->renderException($event->exception()),
                    $call
                );
            } elseif ($event instanceof ConsumedEventInterface) {
                $rendered[] = sprintf('    - %s finished iterating', $call);
            } elseif ($event instanceof NullEventInterface) {
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
