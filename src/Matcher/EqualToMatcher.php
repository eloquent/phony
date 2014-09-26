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

use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Comparator\Factory;

/**
 * A matcher that tests if the value is equal to (==) another value.
 */
class EqualToMatcher extends AbstractMatcher
{
    /**
     * Construct a new equal to matcher.
     *
     * @param mixed $value The value to check against.
     * @param Factory|null The comparator factory to use.
     */
    public function __construct($value, Factory $comparatorFactory = null)
    {
        if (null === $comparatorFactory) {
            $comparatorFactory = new Factory();
        }

        $this->value = $value;
        $this->comparatorFactory = $comparatorFactory;
    }

    /**
     * Get the value.
     *
     * @return mixed The value.
     */
    public function value()
    {
        return $this->value;
    }

    /**
     * Get the comparator factory.
     *
     * @return Factory The comparator factory.
     */
    public function comparatorFactory()
    {
        return $this->comparatorFactory;
    }

    /**
     * Returns true if the supplied value matches.
     *
     * @param mixed $value The value to check.
     *
     * @return boolean True if the value matches.
     */
    public function matches($value)
    {
        $comparator = $this->comparatorFactory
            ->getComparatorFor($this->value, $value);

        try {
            $comparator->assertEquals($this->value, $value);
        } catch (ComparisonFailure $e) {
            return false;
        }

        return true;
    }

    /**
     * Describe this matcher.
     *
     * @return string The description.
     */
    public function describe()
    {
        return var_export($this->value, true);
    }

    private $value;
    private $comparatorFactory;
}
