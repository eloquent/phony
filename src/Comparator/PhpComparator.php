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
 * A comparator that compares using the built-in PHP less than operator.
 */
class PhpComparator implements ComparatorInterface
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
     * @param mixed $left  The first value to compare.
     * @param mixed $right The second value to compare.
     *
     * @return integer The result of the comparison.
     */
    public function compare($left, $right)
    {
        if ($left < $right) {
            return -1;
        } elseif ($right < $left) {
            return +1;
        }

        return 0;
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
     * @param mixed $left  The first value to compare.
     * @param mixed $right The second value to compare.
     *
     * @return integer The result of the comparison.
     */
    public function __invoke($left, $right)
    {
        return $this->compare($left, $right);
    }

    private static $instance;
}
