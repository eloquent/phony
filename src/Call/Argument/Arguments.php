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

use ArrayIterator;
use Eloquent\Phony\Call\Argument\Exception\UndefinedArgumentException;
use Eloquent\Phony\Collection\Exception\UndefinedIndexException;
use Eloquent\Phony\Collection\IndexNormalizer;
use Eloquent\Phony\Collection\IndexNormalizerInterface;
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
     * @param array<integer,mixed>|null     $arguments       The arguments.
     * @param IndexNormalizerInterface|null $indexNormalizer The index normalizer to use.
     */
    public function __construct(
        array $arguments = null,
        IndexNormalizerInterface $indexNormalizer = null
    ) {
        if (null === $arguments) {
            $arguments = array();
        }
        if (null === $indexNormalizer) {
            $indexNormalizer = IndexNormalizer::instance();
        }

        $this->arguments = $arguments;
        $this->indexNormalizer = $indexNormalizer;
        $this->count = count($arguments);
    }

    /**
     * Get the index normalizer.
     *
     * @return IndexNormalizerInterface The index normalizer.
     */
    public function indexNormalizer()
    {
        return $this->indexNormalizer;
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

        try {
            $normalized = $this->indexNormalizer
                ->normalize($this->count, $index);
        } catch (UndefinedIndexException $e) {
            throw new UndefinedArgumentException($index, $e);
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
        if ($this->indexNormalizer->tryNormalize($this->count, $index)) {
            return true;
        }

        return false;
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
        try {
            $normalized = $this->indexNormalizer
                ->normalize($this->count, $index);
        } catch (UndefinedIndexException $e) {
            throw new UndefinedArgumentException($index, $e);
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

    private $arguments;
    private $indexNormalizer;
    private $count;
}
