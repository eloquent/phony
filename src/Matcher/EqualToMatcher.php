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

use Eloquent\Phony\Comparator\ComparatorInterface;
use Eloquent\Phony\Comparator\DeepComparator;

/**
 * A matcher that tests if the value is equal to (==) another value.
 */
class EqualToMatcher extends AbstractMatcher
{
    /**
     * Construct a new equal to matcher.
     *
     * @param mixed $value The value to check against.
     */
    public function __construct($value, ComparatorInterface $comparator = null)
    {
        if (null === $comparator) {
            $comparator = DeepComparator::instance();
        }

        $this->value = $value;
        $this->comparator = $comparator;
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
     * Get the comparator.
     *
     * @return ComparatorInterface The comparator.
     */
    public function comparator()
    {
        return $this->comparator;
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
        return 0 === $this->comparator->compare($value, $this->value);
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
}
