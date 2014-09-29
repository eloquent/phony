<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Matcher\Factory;

use Eloquent\Phony\Matcher\AnyMatcher;
use Eloquent\Phony\Matcher\EqualToMatcher;
use Eloquent\Phony\Matcher\MatcherDriverInterface;
use Eloquent\Phony\Matcher\MatcherInterface;
use Eloquent\Phony\Matcher\WildcardMatcher;
use Eloquent\Phony\Matcher\WildcardMatcherInterface;

/**
 * Creates matchers.
 *
 * @internal
 */
class MatcherFactory implements MatcherFactoryInterface
{
    /**
     * Get the static instance of this factory.
     *
     * @return MatcherFactoryInterface The static factory.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Construct a new matcher factory.
     *
     * @param array<MatcherDriverInterface>|null $drivers            The matcher drivers to use.
     * @param MatcherInterface|null              $anyMatcher         A matcher that matches any value.
     * @param WildcardMatcherInterface|null      $wildcardAnyMatcher A matcher that matches any number of arguments of any value.
     */
    public function __construct(
        array $drivers = null,
        MatcherInterface $anyMatcher = null,
        WildcardMatcherInterface $wildcardAnyMatcher = null
    ) {
        if (null === $drivers) {
            $drivers = array();
        }
        if (null === $anyMatcher) {
            $anyMatcher = AnyMatcher::instance();
        }
        if (null === $wildcardAnyMatcher) {
            $wildcardAnyMatcher = WildcardMatcher::instance();
        }

        $this->drivers = $drivers;
        $this->anyMatcher = $anyMatcher;
        $this->wildcardAnyMatcher = $wildcardAnyMatcher;
    }

    /**
     * Set the matcher drivers.
     *
     * @param array<MatcherDriverInterface> $drivers The matcher drivers.
     */
    public function setMatcherDrivers(array $drivers)
    {
        $this->drivers = $drivers;
    }

    /**
     * Add a matcher driver.
     *
     * @param MatcherDriverInterface $driver The matcher driver.
     */
    public function addMatcherDriver(MatcherDriverInterface $driver)
    {
        if (!in_array($driver, $this->drivers, true)) {
            $this->drivers[] = $driver;
        }
    }

    /**
     * Get the matcher drivers.
     *
     * @return array<MatcherDriverInterface> The matcher drivers.
     */
    public function drivers()
    {
        return $this->drivers;
    }

    /**
     * Returns true if the supplied value is a matcher.
     *
     * @param mixed $value The value to test.
     *
     * @return boolean True if the value is a matcher.
     */
    public function isMatcher($value)
    {
        if ($value instanceof MatcherInterface) {
            return true;
        }

        if (is_object($value)) {
            foreach ($this->drivers as $driver) {
                if ($driver->isSupported($value)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Create a new matcher for the supplied value.
     *
     * @param mixed $value The value to create a matcher for.
     *
     * @return MatcherInterface The newly created matcher.
     */
    public function adapt($value)
    {
        if ($value instanceof MatcherInterface) {
            return $value;
        }

        if (is_object($value)) {
            foreach ($this->drivers as $driver) {
                if ($driver->adapt($value)) {
                    return $value;
                }
            }
        }

        return $this->equalTo($value);
    }

    /**
     * Create new matchers for the all supplied values.
     *
     * @param array<integer,mixed> $values The values to create matchers for.
     *
     * @return array<integer,MatcherInterface> The newly created matchers.
     */
    public function adaptAll(array $values)
    {
        $matchers = array();
        foreach ($values as $value) {
            $matchers[] = $this->adapt($value);
        }

        return $matchers;
    }

    /**
     * Create a new matcher that matches anything.
     *
     * @return MatcherInterface The newly created matcher.
     */
    public function any()
    {
        return $this->anyMatcher;
    }

    /**
     * Create a new equal to matcher.
     *
     * @param mixed $value The value to check.
     *
     * @return MatcherInterface The newly created matcher.
     */
    public function equalTo($value)
    {
        return new EqualToMatcher($value);
    }

    /**
     * Create a new matcher that matches multiple arguments.
     *
     * @param mixed        $value            The value to check for each argument.
     * @param integer|null $minimumArguments The minimum number of arguments.
     * @param integer|null $maximumArguments The maximum number of arguments.
     *
     * @return WildcardMatcherInterface The newly created wildcard matcher.
     */
    public function wildcard(
        $value = null,
        $minimumArguments = null,
        $maximumArguments = null
    ) {
        if (0 === func_num_args()) {
            return $this->wildcardAnyMatcher;
        }

        if (null === $value) {
            $value = $this->any();
        } else {
            $value = $this->adapt($value);
        }

        return
            new WildcardMatcher($value, $minimumArguments, $maximumArguments);
    }

    private static $instance;
    private $drivers;
    private $anyMatcher;
    private $wildcardAnyMatcher;
}
