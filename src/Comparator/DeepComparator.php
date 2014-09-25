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
     * If $relaxClassComparisons is true, class names are not included in the
     * comparison of objects.
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
     * Fetch the fallback comparator.
     *
     * @return ComparatorInterface The comparator to use when the operands are not arrays or objects.
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
     * @param mixed $lhs The first value to compare.
     * @param mixed $rhs The second value to compare.
     *
     * @return integer The result of the comparison.
     */
    public function compare($lhs, $rhs)
    {
        $visitationContext = array();

        return $this->compareValue($lhs, $rhs, $visitationContext);
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
     * @param mixed $lhs
     * @param mixed $rhs
     * @param mixed &$visitationContext
     *
     * @return integer The result of the comparison.
     */
    protected function compareValue($lhs, $rhs, &$visitationContext)
    {
        if (is_array($lhs) && is_array($rhs)) {
            return $this->compareArray($lhs, $rhs, $visitationContext);
        } elseif (is_object($lhs) && is_object($rhs)) {
            return $this->compareObject($lhs, $rhs, $visitationContext);
        }

        return $this->fallbackComparator()->compare($lhs, $rhs);
    }

    /**
     * @param array $lhs
     * @param array $rhs
     * @param mixed &$visitationContext
     *
     * @return integer The result of the comparison.
     */
    protected function compareArray(array $lhs, array $rhs, &$visitationContext)
    {
        reset($lhs);
        reset($rhs);

        while (true) {
            $left  = each($lhs);
            $right = each($rhs);

            if ($left === false && $right === false) {
                break;
            } elseif ($left === false) {
                return -1;
            } elseif ($right === false) {
                return +1;
            }

            $cmp = $this->compareValue($left['key'], $right['key'], $visitationContext);
            if ($cmp !== 0) {
                return $cmp;
            }

            $cmp = $this->compareValue($left['value'], $right['value'], $visitationContext);
            if ($cmp !== 0) {
                return $cmp;
            }
        }

        return 0;
    }

    /**
     * @param object $lhs
     * @param object $rhs
     * @param mixed  &$visitationContext
     *
     * @return integer The result of the comparison.
     */
    protected function compareObject($lhs, $rhs, &$visitationContext)
    {
        if ($lhs === $rhs) {
            return 0;
        } elseif ($this->isNestedComparison($lhs, $rhs, $visitationContext)) {
            return strcmp(
                spl_object_hash($lhs),
                spl_object_hash($rhs)
            );
        } elseif (!$this->relaxClassComparisons) {
            $diff = strcmp(get_class($lhs), get_class($rhs));
            if ($diff !== 0) {
                return $diff;
            }
        }

        return $this->compareArray(
            $this->objectProperties($lhs, $visitationContext),
            $this->objectProperties($rhs, $visitationContext),
            $visitationContext
        );
    }

    /**
     * @param object $object
     * @param mixed  &$visitationContext
     *
     * @return array<string,mixed>
     */
    protected function objectProperties($object, &$visitationContext)
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

    /**
     * @param mixed $lhs
     * @param mixed $rhs
     * @param mixed &$visitationContext
     *
     * @return boolean
     */
    protected function isNestedComparison($lhs, $rhs, &$visitationContext)
    {
        $key = spl_object_hash($lhs) . ':' . spl_object_hash($rhs);

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
