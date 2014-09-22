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

use Eloquent\Phony\Matcher\EqualToMatcher;
use Eloquent\Phony\Matcher\MatcherDriverInterface;
use Eloquent\Phony\Matcher\MatcherInterface;

/**
 * Creates matchers.
 */
class MatcherFactory implements MatcherFactoryInterface
{
    /**
     * Construct a new matcher factory.
     *
     * @param array<MatcherDriverInterface>|null $drivers The matcher drivers to use.
     */
    public function __construct(array $drivers = null)
    {
        if (null === $drivers) {
            $drivers = array();
        }

        $this->drivers = $drivers;
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

    private $drivers;
}
