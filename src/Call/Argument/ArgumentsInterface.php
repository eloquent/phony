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

use Countable;
use Eloquent\Phony\Call\Argument\Exception\UndefinedArgumentException;
use IteratorAggregate;

/**
 * The interface implemented by arguments.
 */
interface ArgumentsInterface extends Countable, IteratorAggregate
{
    /**
     * Get the arguments.
     *
     * This method supports reference parameters.
     *
     * @return array<integer,mixed> The arguments.
     */
    public function all();

    /**
     * Set an argument by index.
     *
     * @param mixed        $value The value.
     * @param integer|null $index The index, or null for the first argument.
     *
     * @throws UndefinedArgumentException If the requested argument is undefined.
     */
    public function set($value, $index = null);

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
