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

use Eloquent\Phony\Integration\CounterpartMatcherDriver;
use Eloquent\Phony\Integration\HamcrestMatcherDriver;
use Eloquent\Phony\Integration\MockeryMatcherDriver;
use Eloquent\Phony\Integration\PhakeMatcherDriver;
use Eloquent\Phony\Integration\ProphecyMatcherDriver;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Phpunit\PhpunitMatcherDriver;
use Eloquent\Phony\Simpletest\SimpletestMatcherDriver;

/**
 * Creates matchers.
 */
class MatcherFactory
{
    /**
     * Get the static instance of this factory.
     *
     * @return MatcherFactory The static factory.
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance =
                new self(AnyMatcher::instance(), WildcardMatcher::instance());
            self::$instance->addDefaultMatcherDrivers();
        }

        return self::$instance;
    }

    /**
     * Construct a new matcher factory.
     *
     * @param Matcher         $anyMatcher         A matcher that matches any value.
     * @param WildcardMatcher $wildcardAnyMatcher A matcher that matches any number of arguments of any value.
     */
    public function __construct(
        Matcher $anyMatcher,
        WildcardMatcher $wildcardAnyMatcher
    ) {
        $this->drivers = array();
        $this->driverIndex = array();
        $this->anyMatcher = $anyMatcher;
        $this->wildcardAnyMatcher = $wildcardAnyMatcher;
    }

    /**
     * Add a matcher driver.
     *
     * @param MatcherDriver $driver The matcher driver.
     */
    public function addMatcherDriver(MatcherDriver $driver)
    {
        if (!in_array($driver, $this->drivers, true)) {
            $this->drivers[] = $driver;

            if ($driver->isAvailable()) {
                foreach ($driver->matcherClassNames() as $className) {
                    $this->driverIndex[$className] = $driver;
                }
            }
        }
    }

    /**
     * Add the default matcher drivers.
     */
    public function addDefaultMatcherDrivers()
    {
        $this->addMatcherDriver(HamcrestMatcherDriver::instance());
        $this->addMatcherDriver(CounterpartMatcherDriver::instance());
        $this->addMatcherDriver(PhpunitMatcherDriver::instance());
        $this->addMatcherDriver(SimpletestMatcherDriver::instance());
        $this->addMatcherDriver(PhakeMatcherDriver::instance());
        $this->addMatcherDriver(ProphecyMatcherDriver::instance());
        $this->addMatcherDriver(MockeryMatcherDriver::instance());
    }

    /**
     * Get the matcher drivers.
     *
     * @return array<MatcherDriver> The matcher drivers.
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
        if (is_object($value)) {
            if ($value instanceof Matcher) {
                return true;
            }

            if ($value instanceof InstanceHandle) {
                return $value->isAdaptable();
            }

            foreach ($this->driverIndex as $className => $driver) {
                if (is_a($value, $className)) {
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
     * @return Matcher The newly created matcher.
     */
    public function adapt($value)
    {
        if (
            $value instanceof Matcher ||
            $value instanceof WildcardMatcher
        ) {
            return $value;
        }

        if (is_object($value)) {
            if ($value instanceof InstanceHandle) {
                if ($value->isAdaptable()) {
                    return new EqualToMatcher($value->mock());
                }
            } else {
                foreach ($this->driverIndex as $className => $driver) {
                    if (is_a($value, $className)) {
                        return $driver->wrapMatcher($value);
                    }
                }
            }
        }

        if ('*' === $value) {
            return $this->wildcardAnyMatcher;
        }

        if ('~' === $value) {
            return $this->anyMatcher;
        }

        return new EqualToMatcher($value);
    }

    /**
     * Create new matchers for the all supplied values.
     *
     * @param array $values The values to create matchers for.
     *
     * @return array<Matcher> The newly created matchers.
     */
    public function adaptAll(array $values)
    {
        $matchers = array();

        foreach ($values as $value) {
            if (
                $value instanceof Matcher ||
                $value instanceof WildcardMatcher
            ) {
                $matchers[] = $value;

                continue;
            }

            if (is_object($value)) {
                if ($value instanceof InstanceHandle) {
                    if ($value->isAdaptable()) {
                        $matchers[] = new EqualToMatcher($value->mock());

                        continue;
                    }
                } else {
                    foreach ($this->driverIndex as $className => $driver) {
                        if (is_a($value, $className)) {
                            $matchers[] = $driver->wrapMatcher($value);

                            continue 2;
                        }
                    }
                }
            }

            if ('*' === $value) {
                $matchers[] = $this->wildcardAnyMatcher;
            } elseif ('~' === $value) {
                $matchers[] = $this->anyMatcher;
            } else {
                $matchers[] = new EqualToMatcher($value);
            }
        }

        return $matchers;
    }

    /**
     * Create a new matcher that matches anything.
     *
     * @return Matcher The newly created matcher.
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
     * @return Matcher The newly created matcher.
     */
    public function equalTo($value)
    {
        return new EqualToMatcher($value);
    }

    /**
     * Create a new matcher that matches multiple arguments.
     *
     * @param mixed        $value            The value to check for each argument.
     * @param integer      $minimumArguments The minimum number of arguments.
     * @param integer|null $maximumArguments The maximum number of arguments.
     *
     * @return WildcardMatcher The newly created wildcard matcher.
     */
    public function wildcard(
        $value = null,
        $minimumArguments = 0,
        $maximumArguments = null
    ) {
        if (0 === func_num_args()) {
            return $this->wildcardAnyMatcher;
        }

        if (null === $value) {
            $value = $this->anyMatcher;
        } else {
            $value = $this->adapt($value);
        }

        return
            new WildcardMatcher($value, $minimumArguments, $maximumArguments);
    }

    private static $instance;
    private $drivers;
    private $driverIndex;
    private $anyMatcher;
    private $wildcardAnyMatcher;
}
