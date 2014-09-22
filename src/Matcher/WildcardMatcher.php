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
 * A matcher that tests any number of arguments against another matcher.
 */
class WildcardMatcher implements WildcardMatcherInterface
{
    /**
     * Get the static instance of this matcher.
     *
     * @return WildcardMatcherInterface The static matcher.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Construct a new wildcard matcher.
     *
     * @param MatcherInterface|null $matcher          The matcher to use for each argument.
     * @param integer|null          $minimumArguments The minimum number of arguments.
     * @param integer|null          $maximumArguments The maximum number of arguments.
     */
    public function __construct(
        $matcher = null,
        $minimumArguments = null,
        $maximumArguments = null
    ) {
        if (null === $matcher) {
            $matcher = AnyMatcher::instance();
        }
        if (null === $minimumArguments) {
            $minimumArguments = 0;
        }

        $this->matcher = $matcher;
        $this->minimumArguments = $minimumArguments;
        $this->maximumArguments = $maximumArguments;
    }

    /**
     * Get the matcher to use for each argument.
     *
     * @return MatcherInterface The matcher.
     */
    public function matcher()
    {
        return $this->matcher;
    }

    /**
     * Get the minimum number of arguments to match.
     *
     * @return integer The minimum number of arguments.
     */
    public function minimumArguments()
    {
        return $this->minimumArguments;
    }

    /**
     * Get the maximum number of arguments to match.
     *
     * @return integer|null The maximum number of arguments.
     */
    public function maximumArguments()
    {
        return $this->maximumArguments;
    }

    private static $instance;
    private $matcher;
    private $minimumArguments;
    private $maximumArguments;
}
