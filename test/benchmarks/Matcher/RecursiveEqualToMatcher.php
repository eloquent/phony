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
use Exception;

/**
 * A matcher that tests if the value is strictly equal to (===) another
 * value. Arrays and objects are descending into, comparing each key/value
 * pair individually.
 *
 * This implementation is only provided to benchmark against.
 *
 * @internal
 */
class RecursiveEqualToMatcher extends AbstractMatcher
{
    /**
     * Construct a new equal to matcher.
     *
     * @param mixed $value The value to check against.
     * @param ExporterInterface|null The exporter to use.
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
        $this->previous = array();

        try {
            return $this->compare($this->value, $value);
        } catch (Exception $e) {
            $this->cleanup();

            throw $e;
        }

        $this->cleanup();
    }

    private function compare(&$left, &$right)
    {
        if (is_array($left) && is_array($right)) {
            if ($this->isNested($left, $right)) {
                return true;
            }

            while (true) {
                while (true) {
                    $leftKey = key($left);

                    if ($leftKey !== self::ARRAY_ID_KEY) {
                        break;
                    }

                    next($left);
                }

                next($left);

                while (true) {
                    $rightKey = key($right);

                    if ($rightKey !== self::ARRAY_ID_KEY) {
                        break;
                    }

                    next($right);
                }

                next($right);

                if (null === $leftKey && null === $rightKey) {
                    return true; // end of both arrays
                } elseif (null === $leftKey) {
                    return false; // count($left) < count($right)
                } elseif (null === $rightKey) {
                    return false; // count($left) > count($right)
                } elseif ($leftKey !== $rightKey) {
                    return false; // keys differ
                } elseif (!$this->compare($left[$leftKey], $right[$rightKey])) {
                    return false; // values differ
                }
            }
        } elseif (!is_object($left) || !is_object($right)) {
            return $left === $right;
        } elseif (get_class($left) !== get_class($right)) {
            return false;
        } elseif ($this->isNested($left, $right)) {
            return true; // ???
        } else {
            if ($left instanceof Exception) {
                $left = (array) $left;
                // @codeCoverageIgnoreStart
                unset(
                    $left["\x00gcdata"],
                    $left["\x00*\x00file"],
                    $left["\x00*\x00line"],
                    $left["\x00Exception\x00trace"],
                    $left["\x00Exception\x00string"],
                    $left["\x00Exception\x00xdebug_message"]
                );
                // @codeCoverageIgnoreEnd
            } else {
                $left = (array) $left;
                unset($left["\x00gcdata"]);
            }

            if ($right instanceof Exception) {
                $right = (array) $right;
                // @codeCoverageIgnoreStart
                unset(
                    $right["\x00gcdata"],
                    $right["\x00*\x00file"],
                    $right["\x00*\x00line"],
                    $right["\x00Exception\x00trace"],
                    $right["\x00Exception\x00string"],
                    $right["\x00Exception\x00xdebug_message"]
                );
                // @codeCoverageIgnoreEnd
            } else {
                $right = (array) $right;
                unset($right["\x00gcdata"]);
            }

            if ($this->compare($left, $right)) {
                return true;
            }
        }

        return false;
    }

    private function isNested(&$left, &$right)
    {
        if (is_array($left) && is_array($right)) {
            $key = $this->arrayId($left) . ':' . $this->arrayId($right);
        } else {
            $key = spl_object_hash($left) . ':' . spl_object_hash($right);
        }

        if (isset($this->previous[$key])) {
            return true;
        }

        $this->previous[$key] = true;

        return false;
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

    private function arrayId(array &$array)
    {
        if (isset($array[self::ARRAY_ID_KEY])) {
            return $array[self::ARRAY_ID_KEY];
        }

        $this->cleanupArrayIds[] = &$array;

        return $array[self::ARRAY_ID_KEY] = count($this->cleanupArrayIds);
    }

    private function cleanup()
    {
        foreach ($this->cleanupArrayIds as &$array) {
            unset($array[self::ARRAY_ID_KEY]);
        }
    }

    const ARRAY_ID_KEY = "\x00__phony__\x00";

    private $previous;
    private $cleanupArrayIds = array();
    private $value;
    private $exporter;
}
