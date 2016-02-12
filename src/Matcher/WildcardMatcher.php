<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
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
     * @param integer               $minimumArguments The minimum number of arguments.
     * @param integer|null          $maximumArguments The maximum number of arguments.
     */
    public function __construct(
        MatcherInterface $matcher = null,
        $minimumArguments = 0,
        $maximumArguments = null
    ) {
        if (null === $matcher) {
            $matcher = AnyMatcher::instance();
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

    /**
     * Describe this matcher.
     *
     * @return string The description.
     */
    public function describe()
    {
        $matcherDescription = $this->matcher->describe();

        if (0 === $this->minimumArguments) {
            if (null === $this->maximumArguments) {
                return sprintf('%s*', $matcherDescription);
            } else {
                return sprintf(
                    '%s{,%d}',
                    $matcherDescription,
                    $this->maximumArguments
                );
            }
        } elseif (null === $this->maximumArguments) {
            return sprintf(
                '%s{%d,}',
                $matcherDescription,
                $this->minimumArguments
            );
        } elseif ($this->minimumArguments === $this->maximumArguments) {
            return sprintf(
                '%s{%d}',
                $matcherDescription,
                $this->minimumArguments
            );
        }

        return sprintf(
            '%s{%d,%d}',
            $matcherDescription,
            $this->minimumArguments,
            $this->maximumArguments
        );
    }

    /**
     * Describe this matcher.
     *
     * @return string The description.
     */
    public function __toString()
    {
        return $this->describe();
    }

    private static $instance;
    private $matcher;
    private $minimumArguments;
    private $maximumArguments;
}
