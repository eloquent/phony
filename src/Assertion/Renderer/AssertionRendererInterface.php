<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Assertion\Renderer;

use Eloquent\Phony\Call\CallInterface;
use Eloquent\Phony\Matcher\MatcherInterface;

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
     * Render a sequence of matchers.
     *
     * @param array<integer,MatcherInterface> $matchers The matchers.
     *
     * @return string The rendered matchers.
     */
    public function renderMatchers(array $matchers);

    /**
     * Render a sequence of calls.
     *
     * @param array<integer,CallInterface> $calls The calls.
     *
     * @return string The rendered calls.
     */
    public function renderCalls(array $calls);

    /**
     * Render a only the arguments of a sequence of calls.
     *
     * @param array<integer,CallInterface> $calls The calls.
     *
     * @return string The rendered call arguments.
     */
    public function renderCallsArguments(array $calls);

    /**
     * Render a only the return values of a sequence of calls.
     *
     * @param array<integer,CallInterface> $calls The calls.
     *
     * @return string The rendered call return values.
     */
    public function renderReturnValues(array $calls);

    /**
     * Render a only the $this values of a sequence of calls.
     *
     * @param array<integer,CallInterface> $calls The calls.
     *
     * @return string The rendered call $this values.
     */
    public function renderThisValues(array $calls);

    /**
     * Render the supplied call.
     *
     * @param CallInterface $call The call.
     *
     * @return string The rendered call.
     */
    public function renderCall(CallInterface $call);

    /**
     * Render a sequence of arguments.
     *
     * @param array<integer,mixed> $arguments The arguments.
     *
     * @return string The rendered arguments.
     */
    public function renderArguments(array $arguments);
}
