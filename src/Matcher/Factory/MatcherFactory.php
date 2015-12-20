<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Matcher\Factory;

use Eloquent\Phony\Integration\Counterpart\CounterpartMatcherDriver;
use Eloquent\Phony\Integration\Hamcrest\HamcrestMatcherDriver;
use Eloquent\Phony\Integration\Mockery\MockeryMatcherDriver;
use Eloquent\Phony\Integration\Phake\PhakeMatcherDriver;
use Eloquent\Phony\Integration\Phpunit\PhpunitMatcherDriver;
use Eloquent\Phony\Integration\Prophecy\ProphecyMatcherDriver;
use Eloquent\Phony\Integration\Simpletest\SimpletestMatcherDriver;
use Eloquent\Phony\Matcher\AnyMatcher;
use Eloquent\Phony\Matcher\Driver\MatcherDriverInterface;
use Eloquent\Phony\Matcher\EqualToMatcher;
use Eloquent\Phony\Matcher\MatcherInterface;
use Eloquent\Phony\Matcher\WildcardMatcher;
use Eloquent\Phony\Matcher\WildcardMatcherInterface;

/**
 * Creates matchers.
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
            self::$instance->addDefaultMatcherDrivers();
        }

        return self::$instance;
    }

    /**
     * Construct a new matcher factory.
     *
     * @param array<MatcherDriverInterface> $drivers            The matcher drivers to use.
     * @param MatcherInterface|null         $anyMatcher         A matcher that matches any value.
     * @param WildcardMatcherInterface|null $wildcardAnyMatcher A matcher that matches any number of arguments of any value.
     */
    public function __construct(
        array $drivers = array(),
        MatcherInterface $anyMatcher = null,
        WildcardMatcherInterface $wildcardAnyMatcher = null
    ) {
        if (null === $anyMatcher) {
            $anyMatcher = AnyMatcher::instance();
        }
        if (null === $wildcardAnyMatcher) {
            $wildcardAnyMatcher = WildcardMatcher::instance();
        }

        $this->drivers = array();
        $this->driverIndex = array();
        $this->anyMatcher = $anyMatcher;
        $this->wildcardAnyMatcher = $wildcardAnyMatcher;

        foreach ($drivers as $driver) {
            $this->addMatcherDriver($driver);
        }
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
     * @return MatcherInterface The newly created matcher.
     */
    public function adapt($value)
    {
        if (
            $value instanceof MatcherInterface ||
            $value instanceof WildcardMatcherInterface
        ) {
            return $value;
        }

        if (is_object($value)) {
            foreach ($this->driverIndex as $className => $driver) {
                if (is_a($value, $className)) {
                    return $driver->wrapMatcher($value);
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
     * @return array<MatcherInterface> The newly created matchers.
     */
    public function adaptAll(array $values)
    {
        $matchers = array();

        foreach ($values as $value) {
            if (
                $value instanceof MatcherInterface ||
                $value instanceof WildcardMatcherInterface
            ) {
                $matchers[] = $value;

                continue;
            }

            if (is_object($value)) {
                foreach ($this->driverIndex as $className => $driver) {
                    if (is_a($value, $className)) {
                        $matchers[] = $driver->wrapMatcher($value);

                        continue 2;
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
     * @param integer      $minimumArguments The minimum number of arguments.
     * @param integer|null $maximumArguments The maximum number of arguments.
     *
     * @return WildcardMatcherInterface The newly created wildcard matcher.
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
