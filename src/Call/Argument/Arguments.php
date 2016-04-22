<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Argument;

use ArrayIterator;
use Countable;
use Eloquent\Phony\Call\Argument\Exception\UndefinedArgumentException;
use Iterator;
use IteratorAggregate;

/**
 * Represents a set of call arguments.
 *
 * @api
 */
class Arguments implements Countable, IteratorAggregate
{
    /**
     * Create a new set of call arguments from the supplied arguments.
     *
     * @param mixed ...$arguments The arguments.
     *
     * @return Arguments The arguments object.
     */
    public static function create()
    {
        return new self(func_get_args());
    }

    /**
     * Construct a new set of call arguments.
     *
     * @param array $arguments The arguments.
     */
    public function __construct(array $arguments)
    {
        $this->arguments = $arguments;
        $this->count = count($arguments);
    }

    /**
     * Copy these arguments, breaking any references.
     *
     * @api
     *
     * @return Arguments The copied arguments.
     */
    public function copy()
    {
        $arguments = array();

        foreach ($this->arguments as $argument) {
            $arguments[] = $argument;
        }

        return new self($arguments);
    }

    /**
     * Get the arguments.
     *
     * This method supports reference parameters.
     *
     * @api
     *
     * @return array<mixed> The arguments.
     */
    public function all()
    {
        return $this->arguments;
    }

    /**
     * Set an argument by index.
     *
     * If called with no arguments, sets the first argument to null.
     *
     * If called with one argument, sets the first argument to `$indexOrValue`.
     *
     * If called with two arguments, sets the argument at `$indexOrValue` to
     * `$value`.
     *
     * @api
     *
     * @param mixed $indexOrValue The index, or value if no index is specified.
     * @param mixed $value        The value.
     *
     * @return $this                      This arguments object.
     * @throws UndefinedArgumentException If the requested argument is undefined.
     */
    public function set($indexOrValue = null, $value = null)
    {
        if (func_num_args() > 1) {
            $index = $indexOrValue;
        } else {
            $index = 0;
            $normalized = 0;
            $value = $indexOrValue;
        }

        if (!$this->normalizeIndex($this->count, $index, $normalized)) {
            throw new UndefinedArgumentException($index);
        }

        $this->arguments[$normalized] = $value;

        return $this;
    }

    /**
     * Returns true if the argument index exists.
     *
     * Negative indices are offset from the end of the list. That is, `-1`
     * indicates the last element, and `-2` indicates the second last element.
     *
     * @api
     *
     * @param integer $index The index.
     *
     * @return boolean True if the argument exists.
     */
    public function has($index = 0)
    {
        if ($this->normalizeIndex($this->count, $index)) {
            return true;
        }

        return false;
    }

    /**
     * Get an argument by index.
     *
     * Negative indices are offset from the end of the list. That is, `-1`
     * indicates the last element, and `-2` indicates the second last element.
     *
     * @api
     *
     * @param integer $index The index.
     *
     * @return mixed                      The argument.
     * @throws UndefinedArgumentException If the requested argument is undefined.
     */
    public function get($index = 0)
    {
        if (!$this->normalizeIndex($this->count, $index, $normalized)) {
            throw new UndefinedArgumentException($index);
        }

        return $this->arguments[$normalized];
    }

    /**
     * Get the number of arguments.
     *
     * @api
     *
     * @return integer The number of arguments.
     */
    public function count()
    {
        return $this->count;
    }

    /**
     * Get an iterator for these arguments.
     *
     * @return Iterator The iterator.
     */
    public function getIterator()
    {
        return new ArrayIterator($this->arguments);
    }

    private function normalizeIndex($size, $index, &$normalized = null)
    {
        $normalized = null;

        if ($index < 0) {
            $potential = $size + $index;

            if ($potential < 0) {
                return false;
            }
        } else {
            $potential = $index;
        }

        if ($potential >= $size) {
            return false;
        }

        $normalized = $potential;

        return true;
    }

    private $arguments;
    private $count;
}
