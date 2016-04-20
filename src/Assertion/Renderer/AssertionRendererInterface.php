<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Assertion\Renderer;

use Eloquent\Phony\Call\Argument\ArgumentsInterface;
use Eloquent\Phony\Call\CallInterface;
use Eloquent\Phony\Call\Event\CalledEventInterface;
use Eloquent\Phony\Cardinality\CardinalityInterface;
use Eloquent\Phony\Event\EventCollectionInterface;
use Eloquent\Phony\Matcher\MatcherInterface;
use Eloquent\Phony\Mock\Handle\HandleInterface;
use Error;
use Exception;

/**
 * The interface implemented by assertion renderers.
 */
interface AssertionRendererInterface
{
    /**
     * Render a value.
     *
     * @param mixed $value The value.
     *
     * @return string The rendered value.
     */
    public function renderValue($value);

    /**
     * Render a mock.
     *
     * @param HandleInterface $handle The handle.
     *
     * @return string The rendered mock.
     */
    public function renderMock(HandleInterface $handle);

    /**
     * Render a callable.
     *
     * @param callable $callback The callable.
     *
     * @return string The rendered callable.
     */
    public function renderCallable($callback);

    /**
     * Render a sequence of matchers.
     *
     * @param array<MatcherInterface> $matchers The matchers.
     *
     * @return string The rendered matchers.
     */
    public function renderMatchers(array $matchers);

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
    );

    /**
     * Render a sequence of calls.
     *
     * @param array<CallInterface> $calls The calls.
     *
     * @return string The rendered calls.
     */
    public function renderCalls(array $calls);

    /**
     * Render the $this values of a sequence of calls.
     *
     * @param array<CallInterface> $calls The calls.
     *
     * @return string The rendered call $this values.
     */
    public function renderThisValues(array $calls);

    /**
     * Render the arguments of a sequence of calls.
     *
     * @param array<CallInterface> $calls The calls.
     *
     * @return string The rendered call arguments.
     */
    public function renderCallsArguments(array $calls);

    /**
     * Render the responses of a sequence of calls.
     *
     * @param array<CallInterface> $calls              The calls.
     * @param boolean              $expandTraversables True if traversable events should be rendered.
     *
     * @return string The rendered call responses.
     */
    public function renderResponses(array $calls, $expandTraversables = false);

    /**
     * Render the supplied call.
     *
     * @param CallInterface $call The call.
     *
     * @return string The rendered call.
     */
    public function renderCall(CallInterface $call);

    /**
     * Render the supplied 'called' event.
     *
     * @param CalledEventInterface $event The 'called' event.
     *
     * @return string The rendered event.
     */
    public function renderCalledEvent(CalledEventInterface $event);

    /**
     * Render the supplied call's response.
     *
     * @param CallInterface $call The call.
     *
     * @return string The rendered response.
     */
    public function renderResponse(CallInterface $call);

    /**
     * Render the traversable events of a call.
     *
     * @param CallInterface $call The call.
     *
     * @return string The rendered traversable events.
     */
    public function renderProduced(CallInterface $call);

    /**
     * Render a sequence of arguments.
     *
     * @param ArgumentsInterface $arguments The arguments.
     *
     * @return string The rendered arguments.
     */
    public function renderArguments(ArgumentsInterface $arguments);

    /**
     * Render an exception.
     *
     * @param Exception|Error $exception The exception.
     *
     * @return string The rendered exception.
     */
    public function renderException($exception);

    /**
     * Render an arbitrary sequence of events.
     *
     * @param EventCollectionInterface $events The events.
     *
     * @return string The rendered events.
     */
    public function renderEvents(EventCollectionInterface $events);
}
