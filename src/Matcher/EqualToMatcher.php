<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Matcher;

use Eloquent\Phony\Exporter\ExporterInterface;
use Eloquent\Phony\Exporter\InlineExporter;
use Eloquent\Phony\Mock\MockInterface;
use Exception;

/**
 * A matcher that tests if the value is strictly equal to (===) another
 * value. Arrays and objects are descending into, comparing each key/value
 * pair individually.
 *
 * @internal
 */
class EqualToMatcher extends AbstractMatcher
{
    /**
     * Construct a new equal to matcher.
     *
     * @param mixed                  $value    The value to check against.
     * @param ExporterInterface|null $exporter The exporter to use.
     */
    public function __construct(
        $value,
        ExporterInterface $exporter = null
    ) {
        if (null === $exporter) {
            $exporter = InlineExporter::instance();
        }

        $this->value = $value;
        $this->exporter = $exporter;
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
     * Get the exporter.
     *
     * @return ExporterInterface The exporter.
     */
    public function exporter()
    {
        return $this->exporter;
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
        $left = $this->value;
        $right = $value;

        /*
         * @var array<string, boolean> The set of object comparisons that have been made.
         *
         * Keys are of the format "<left spl hash>:<right spl hash>"; values are
         * always TRUE. This set is used to detect comparisons that have already
         * been made in order to avoid infinite recursion when comparing cyclic
         * data structures.
         */
        $visitedObjects = array();

        /*
         * @var array<string, boolean> The set of array comparisons that have been made.
         *
         * Keys are of the format "<left id>:<right id>"; values are always
         * TRUE. This set is used to detect comparisons that have already been
         * made in order to avoid infinite recursion when comparing cyclic data
         * structures.
         */
        $visitedArrays = array();

        /*
         * @var array<&array> Arrays that have been marked with an internal ID.
         *
         * In order to detect cyclic arrays we need to mark them with an ID.
         * This ID must be removed upon completion of the comparison.
         */
        $markedArrays = array();

        /*
         * @var array<&array> Stacks of values that are currently being compared.
         *
         * We maintain our own stack in order to:
         *
         *  - make fewer function calls (much faster in PHP 5.*)
         *  - avoid stack-depth limits
         *  - allow for 'continuations'
         *  - avoid unwinding when comparison fails
         *
         * Separate stacks are used for left/right to avoid construction of
         * temporary arrays.
         */
        $leftStack = array();
        $rightStack = array();

        /*
         * @var integer The number of elements on the stacks.
         */
        $stackSize = 0;

        // ---------------------------------------------------------
        // This is the main entry point of the comparison algorithm.
        // ---------------------------------------------------------
        compare:

        if (is_array($left) && is_array($right)) {

            // Fetch or generate a unique ID for the left-hand-side.
            if (isset($left[self::ARRAY_ID_KEY])) {
                $leftId = $left[self::ARRAY_ID_KEY];
            } else {
                reset($left);
                $leftId = count($markedArrays) + 1;
                $left[self::ARRAY_ID_KEY] = $leftId;
                $markedArrays[] = &$left;
            }

            // Fetch or generate a unique ID for the right-hand-side.
            if (isset($right[self::ARRAY_ID_KEY])) {
                $rightId = $right[self::ARRAY_ID_KEY];
            } else {
                reset($right);
                $rightId = count($markedArrays) + 1;
                $right[self::ARRAY_ID_KEY] = $rightId;
                $markedArrays[] = &$right;
            }

            // Left and right are references to the same array.
            if ($leftId === $rightId) {
                goto pass;
            }

            $comparisonId = $leftId . ':' . $rightId;

            // These two arrays have already been compared.
            if (isset($visitedArrays[$comparisonId])) {
                goto pass;
            }

            // Record the comparison.
            $visitedArrays[$comparisonId] = true;

            // ---------------------------------
            // Compare the next array key/value.
            // ---------------------------------
            compareNextArrayElement:

            // Get the current key for each array, skipping our internal ID
            // value.
            $leftKey = key($left);
            next($left);

            if ($leftKey === self::ARRAY_ID_KEY) {
                $leftKey = key($left);
                next($left);
            }

            $rightKey = key($right);
            next($right);

            if ($rightKey === self::ARRAY_ID_KEY) {
                $rightKey = key($right);
                next($right);
            }

            // Keys can only be string|integer (or null, if end of array).
            // Compare them using regular PHP comparison.
            if ($leftKey !== $rightKey) {
                return false;

            // Both keys are null, which means that both array are the same
            // length.
            } elseif (null === $leftKey) {
                goto pass;
            }

            // Push the arrays we're comparing on to the stack and start
            // comparing the values of this element.
            $leftStack[$stackSize] = &$left;
            $rightStack[$stackSize] = &$right;
            ++$stackSize;

            $left = &$left[$leftKey];
            $right = &$right[$rightKey];

            goto compare;
        }

        // Objects and other non-arrays can be compared with ===, as it will
        // not recurse in either case.
        if ($left === $right) {
            goto pass;
        }

        // Non-objects are not identical.
        if (!is_object($left) && !is_object($right)) {
            return false;
        }

        $leftClass = get_class($left);
        $rightClass = get_class($right);

        // The class names do not match.
        if ($leftClass !== $rightClass) {
            return false;
        }

        $comparisonId = spl_object_hash($left) . ':' . spl_object_hash($right);

        // These two objects have already been compared.
        if (isset($visitedObjects[$comparisonId])) {
            goto pass;
        }

        // Record the comparison.
        $visitedObjects[$comparisonId] = true;

        /*
         * Cast the objects as arrays and start comparing them.
         *
         * Importantly, the array cast operator maintains private and protected
         * properties, as well as arbitrary properties added to the object after
         * construction.
         *
         * Some special properties are removed for the purposes of comparison.
         *
         * @see https://github.com/php/php-src/commit/5721132
         */

        $leftIsMock = $left instanceof MockInterface;
        $leftIsException = $left instanceof Exception;

        $left = (array) $left;
        unset($left["\0gcdata"]);

        if ($leftIsMock) {
            unset($left["\0" . $leftClass . "\0_proxy"]);
        }

        if ($leftIsException) {
            // @codeCoverageIgnoreStart
            unset(
                $left["\0*\0file"],
                $left["\0*\0line"],
                $left["\0Exception\0trace"],
                $left["\0Exception\0string"],
                $left["xdebug_message"]
            );
            // @codeCoverageIgnoreEnd
        }

        $rightIsMock = $right instanceof MockInterface;
        $rightIsException = $right instanceof Exception;

        $right = (array) $right;
        unset($right["\0gcdata"]);

        if ($rightIsMock) {
            unset($right["\0" . $rightClass . "\0_proxy"]);
        }

        if ($rightIsException) {
            // @codeCoverageIgnoreStart
            unset(
                $right["\0*\0file"],
                $right["\0*\0line"],
                $right["\0Exception\0trace"],
                $right["\0Exception\0string"],
                $right["xdebug_message"]
            );
            // @codeCoverageIgnoreEnd
        }

        goto compareNextArrayElement;

        // -----------------------------
        // The current values are equal!
        // -----------------------------
        pass:

        // The stack is not empty, pop some values and compare again.
        if ($stackSize--) {
            $left = &$leftStack[$stackSize];
            $right = &$rightStack[$stackSize];

            goto compareNextArrayElement;
        }

        // Stack is empty, there's nothing left to compare. Clean up the
        // injected array IDs and return
        foreach ($markedArrays as &$array) {
            unset($array[self::ARRAY_ID_KEY]);
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
        return $this->exporter->export($this->value);
    }

    const ARRAY_ID_KEY = "\0__phony__\0";

    private $value;
    private $exporter;
}
