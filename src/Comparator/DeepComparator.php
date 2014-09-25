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

use ReflectionObject;

/**
 * A comparator that performs deep comparison of PHP arrays and objects.
 *
 * Comparison of objects is recursion-safe.
 */
class DeepComparator implements ComparatorInterface
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
     * Construct a new depp comparator.
     *
     * @param ComparatorInterface|null $fallbackComparator    The comparator to use when the operands are not arrays or objects.
     * @param boolean|null             $relaxClassComparisons True to relax class name comparisons; false to compare strictly.
     */
    public function __construct(
        ComparatorInterface $fallbackComparator = null,
        $relaxClassComparisons = null
    ) {
        if (null === $fallbackComparator) {
            $fallbackComparator = StrictPhpComparator::instance();
        }
        if (null === $relaxClassComparisons) {
            $relaxClassComparisons = false;
        }

        $this->fallbackComparator = $fallbackComparator;
        $this->relaxClassComparisons = $relaxClassComparisons;
    }

    /**
     * Get the fallback comparator.
     *
     * @return ComparatorInterface The fallback comparator.
     */
    public function fallbackComparator()
    {
        return $this->fallbackComparator;
    }

    /**
     * Returns true if relaxed class comparisons are in use.
     *
     * @return boolean True if relaxed class comparisons are in use.
     */
    public function relaxClassComparisons()
    {
        return $this->relaxClassComparisons;
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
     * A deep comparison is performed if both operands are arrays, or both are
     * objects; otherwise, the fallback comparator is used.
     *
     * @param mixed $left  The first value to compare.
     * @param mixed $right The second value to compare.
     *
     * @return integer The result of the comparison.
     */
    public function compare($left, $right)
    {
        $visitationContext = array();

        return $this->compareValue($left, $right, $visitationContext);
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
     * A deep comparison is performed if both operands are arrays, or both are
     * objects; otherwise, the fallback comparator is used.
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

    private function compareValue($left, $right, array &$visitationContext)
    {
        if (is_array($left) && is_array($right)) {
            return $this->compareArray($left, $right, $visitationContext);
        } elseif (is_object($left) && is_object($right)) {
            return $this->compareObject($left, $right, $visitationContext);
        }

        return $this->fallbackComparator->compare($left, $right);
    }

    private function compareArray(
        array $left,
        array $right,
        array &$visitationContext
    ) {
        reset($left);
        reset($right);

        while (true) {
            $leftItem  = each($left);
            $rightItem = each($right);

            if ($leftItem === false && $rightItem === false) {
                break;
            } elseif ($leftItem === false) {
                return -1;
            } elseif ($rightItem === false) {
                return +1;
            }

            $cmp = $this->compareValue(
                $leftItem['key'],
                $rightItem['key'],
                $visitationContext
            );

            if ($cmp !== 0) {
                return $cmp;
            }

            $cmp = $this->compareValue(
                $leftItem['value'],
                $rightItem['value'],
                $visitationContext
            );

            if ($cmp !== 0) {
                return $cmp;
            }
        }

        return 0;
    }

    private function compareObject($left, $right, array &$visitationContext)
    {
        if ($left === $right) {
            return 0;
        } elseif (
            $this->isNestedComparison($left, $right, $visitationContext)
        ) {
            return strcmp(spl_object_hash($left), spl_object_hash($right));
        } elseif (!$this->relaxClassComparisons) {
            $diff = strcmp(get_class($left), get_class($right));

            if ($diff !== 0) {
                return $diff;
            }
        }

        return $this->compareArray(
            $this->objectProperties($left, $visitationContext),
            $this->objectProperties($right, $visitationContext),
            $visitationContext
        );
    }

    private function objectProperties($object, array &$visitationContext)
    {
        $properties = array();
        $reflector = new ReflectionObject($object);

        while ($reflector) {
            foreach ($reflector->getProperties() as $property) {
                if ($property->isStatic()) {
                    continue;
                }

                $key = sprintf(
                    '%s::%s',
                    $property->getDeclaringClass()->getName(),
                    $property->getName()
                );

                $property->setAccessible(true);
                $properties[$key] = $property->getValue($object);
            }

            $reflector = $reflector->getParentClass();
        }

        return $properties;
    }

    private function isNestedComparison(
        $left,
        $right,
        array &$visitationContext
    ) {
        $key = spl_object_hash($left) . ':' . spl_object_hash($right);

        if (array_key_exists($key, $visitationContext)) {
            return true;
        }

        $visitationContext[$key] = true;

        return false;
    }

    private static $instance;
    private $fallbackComparator;
    private $relaxClassComparisons;
}
