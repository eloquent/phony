<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Matcher;

/**
 * The interface implemented by wildcard matchers.
 */
interface WildcardMatcherInterface
{
    /**
     * Get the matcher to use for each argument.
     *
     * @return MatcherInterface The matcher.
     */
    public function matcher();

    /**
     * Get the minimum number of arguments to match.
     *
     * @return integer The minimum number of arguments.
     */
    public function minimumArguments();

    /**
     * Get the maximum number of arguments to match.
     *
     * @return integer|null The maximum number of arguments.
     */
    public function maximumArguments();
}
