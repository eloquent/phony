<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Comparator;

/**
 * A comparator that approximates a type-strict version of the built-in PHP
 * comparison operations.
 */
class StrictPhpComparator implements ComparatorInterface
{
    /**
     * Get the static instance of this comparator.
     *
     * @return ComparatorInterface The static comparator.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * If $relaxNumericComparisons is true, integers and doubles are compared as
     * though they were the same type. This allows for natural ordering of
     * numbers, ie int(3) < double(4.5) < int(5).
     *
     * @param boolean|null $relaxNumericComparisons True to relax numeric comparisons; false to compare strictly.
     */
    public function __construct($relaxNumericComparisons = null)
    {
        if (null === $relaxNumericComparisons) {
            $relaxNumericComparisons = true;
        }

        $this->relaxNumericComparisons = $relaxNumericComparisons;
    }

    /**
     * Compare two values, yielding a result according to the following table:
     *
     * +--------------------+---------------+
     * | Condition          | Result        |
     * +--------------------+---------------+
     * | $this == $value    | $result === 0 |
     * | $this < $value     | $result < 0   |
     * | $this > $value     | $result > 0   |
     * +--------------------+---------------+
     *
     * @param mixed $lhs The first value to compare.
     * @param mixed $rhs The second value to compare.
     *
     * @return integer The result of the comparison.
     */
    public function compare($lhs, $rhs)
    {
        $lhsType = $this->transformTypeName($lhs);
        $rhsType = $this->transformTypeName($rhs);
        $cmp = strcmp($lhsType, $rhsType);

        if ($cmp !== 0) {
            return $cmp;
        } elseif ($lhs < $rhs) {
            return -1;
        } elseif ($rhs < $lhs) {
            return +1;
        }

        return 0;
    }

    /**
     * An alias for compare().
     *
     * @param mixed $lhs The first value to compare.
     * @param mixed $rhs The second value to compare.
     *
     * @return integer The result of the comparison.
     */
    public function __invoke($lhs, $rhs)
    {
        return $this->compare($lhs, $rhs);
    }

    /**
     * @param mixed $value
     *
     * @return string The effective type name to use when comparing types.
     */
    private function transformTypeName($value)
    {
        if (is_object($value)) {
            return 'object:' . get_class($value);
        } elseif (is_integer($value) && $this->relaxNumericComparisons) {
            return 'double';
        }

        return gettype($value);
    }

    private static $instance;
    private $relaxNumericComparisons;
}
