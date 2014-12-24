<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Argument;

use ArrayIterator;
use Eloquent\Phony\Call\Argument\Exception\UndefinedArgumentException;
use Iterator;

/**
 * Represents a set of call arguments.
 *
 * @internal
 */
class Arguments implements ArgumentsInterface
{
    /**
     * Adapt a set of call arguments.
     *
     * @param ArgumentsInterface|array<integer,mixed>|null $arguments The arguments.
     *
     * @return ArgumentsInterface The adapted arguments.
     */
    public static function adapt($arguments)
    {
        if ($arguments instanceof ArgumentsInterface) {
            return $arguments;
        }

        return new static($arguments);
    }

    /**
     * Construct a new set of call arguments.
     *
     * @param array<integer,mixed>|null $arguments The arguments.
     */
    public function __construct(array $arguments = null)
    {
        if (null === $arguments) {
            $arguments = array();
        }

        $this->arguments = $arguments;
        $this->count = count($arguments);
    }

    /**
     * Copy these arguments, breaking any references.
     *
     * @return ArgumentsInterface The copied arguments.
     */
    public function copy()
    {
        $arguments = array();

        foreach ($this->arguments as $argument) {
            $arguments[] = $argument;
        }

        return new static($arguments);
    }

    /**
     * Get the arguments.
     *
     * This method supports reference parameters.
     *
     * @return array<integer,mixed> The arguments.
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
     * If called with one argument, sets the first argument to $indexOrValue.
     *
     * If called with two arguments, sets the argument at $indexOrValue to
     * $value.
     *
     * @param mixed $indexOrValue The index, or value if no index is specified.
     * @param mixed $value        The value.
     *
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

        $normalized = $this->normalizeIndex($index);

        if (null === $normalized) {
            throw new UndefinedArgumentException($index);
        }

        $this->arguments[$normalized] = $value;
    }

    /**
     * Returns true if the argument index exists.
     *
     * @param integer|null $index The index, or null for the first argument.
     *
     * @return boolean True if the argument exists.
     */
    public function has($index = null)
    {
        return null !== $this->normalizeIndex($index);
    }

    /**
     * Get an argument by index.
     *
     * @param integer|null $index The index, or null for the first argument.
     *
     * @return mixed                      The argument.
     * @throws UndefinedArgumentException If the requested argument is undefined.
     */
    public function get($index = null)
    {
        $normalized = $this->normalizeIndex($index);

        if (null === $normalized) {
            throw new UndefinedArgumentException($index);
        }

        return $this->arguments[$normalized];
    }

    /**
     * Get the number of arguments.
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

    /**
     * Returns a normalized index.
     *
     * @param integer|null The index.
     *
     * @return integer|null The normalized index.
     */
    protected function normalizeIndex($index)
    {
        if ($this->count < 1) {
            return;
        }

        if (null === $index) {
            $index = 0;
        } elseif ($index < 0) {
            $index = $this->count + $index;

            if ($index < 0) {
                return;
            }
        }

        if ($index >= $this->count) {
            return;
        }

        return $index;
    }

    private $arguments;
    private $count;
}
