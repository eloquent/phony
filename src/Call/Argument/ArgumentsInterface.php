<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Argument;

use Countable;
use Eloquent\Phony\Call\Argument\Exception\UndefinedArgumentException;
use IteratorAggregate;

/**
 * The interface implemented by arguments.
 */
interface ArgumentsInterface extends Countable, IteratorAggregate
{
    /**
     * Copy these arguments, breaking any references.
     *
     * @return ArgumentsInterface The copied arguments.
     */
    public function copy();

    /**
     * Get the arguments.
     *
     * This method supports reference parameters.
     *
     * @return array The arguments.
     */
    public function all();

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
    public function set($indexOrValue = null, $value = null);

    /**
     * Returns true if the argument index exists.
     *
     * @param integer|null $index The index, or null for the first argument.
     *
     * @return boolean True if the argument exists.
     */
    public function has($index = null);

    /**
     * Get an argument by index.
     *
     * @param integer|null $index The index, or null for the first argument.
     *
     * @return mixed                      The argument.
     * @throws UndefinedArgumentException If the requested argument is undefined.
     */
    public function get($index = null);
}
