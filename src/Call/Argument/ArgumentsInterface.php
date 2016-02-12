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

use Countable;
use Eloquent\Phony\Call\Argument\Exception\UndefinedArgumentException;
use IteratorAggregate;

/**
 * The interface implemented by arguments.
 *
 * @api
 */
interface ArgumentsInterface extends Countable, IteratorAggregate
{
    /**
     * Copy these arguments, breaking any references.
     *
     * @api
     *
     * @return ArgumentsInterface The copied arguments.
     */
    public function copy();

    /**
     * Get the arguments.
     *
     * This method supports reference parameters.
     *
     * @api
     *
     * @return array<mixed> The arguments.
     */
    public function all();

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
    public function set($indexOrValue = null, $value = null);

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
    public function has($index = 0);

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
    public function get($index = 0);
}
