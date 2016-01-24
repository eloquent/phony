<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Matcher\Factory;

use Eloquent\Phony\Matcher\MatcherInterface;
use Eloquent\Phony\Matcher\WildcardMatcherInterface;

/**
 * The interface implemented by matcher factories.
 */
interface MatcherFactoryInterface
{
    /**
     * Returns true if the supplied value is a matcher.
     *
     * @param mixed $value The value to test.
     *
     * @return boolean True if the value is a matcher.
     */
    public function isMatcher($value);

    /**
     * Create a new matcher for the supplied value.
     *
     * @param mixed $value The value to create a matcher for.
     *
     * @return MatcherInterface The newly created matcher.
     */
    public function adapt($value);

    /**
     * Create new matchers for the all supplied values.
     *
     * @param array $values The values to create matchers for.
     *
     * @return array<MatcherInterface> The newly created matchers.
     */
    public function adaptAll(array $values);

    /**
     * Create a new matcher that matches anything.
     *
     * @return MatcherInterface The newly created matcher.
     */
    public function any();

    /**
     * Create a new equal to matcher.
     *
     * @param mixed $value The value to check.
     *
     * @return MatcherInterface The newly created matcher.
     */
    public function equalTo($value);

    /**
     * Create a new matcher that matches multiple arguments.
     *
     * @param mixed        $value            The value to check for each argument.
     * @param integer      $minimumArguments The minimum number of arguments.
     * @param integer|null $maximumArguments The maximum number of arguments.
     *
     * @return WildcardMatcherInterface The newly created wildcard matcher.
     */
    public function wildcard(
        $value = null,
        $minimumArguments = 0,
        $maximumArguments = null
    );
}
