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
use Eloquent\Phony\Call\Argument\Exception\UndefinedArgumentException;
use Eloquent\Phony\Collection\Exception\UndefinedIndexException;
use Eloquent\Phony\Collection\IndexNormalizer;
use Eloquent\Phony\Collection\IndexNormalizerInterface;
use Iterator;

/**
 * Represents a set of call arguments.
 */
class Arguments implements ArgumentsInterface
{
    /**
     * Create a new set of call arguments from the supplied arguments.
     *
     * @param mixed ...$arguments The arguments.
     *
     * @return ArgumentsInterface The arguments object.
     */
    public static function create()
    {
        return new self(func_get_args());
    }

    /**
     * Construct a new set of call arguments.
     *
     * @param array                         $arguments       The arguments.
     * @param IndexNormalizerInterface|null $indexNormalizer The index normalizer to use.
     */
    public function __construct(
        array $arguments = array(),
        IndexNormalizerInterface $indexNormalizer = null
    ) {
        if (!$indexNormalizer) {
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

        return new self($arguments);
    }

    /**
     * Get the arguments.
     *
     * This method supports reference parameters.
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

        try {
            $normalized =
                $this->indexNormalizer->normalize($this->count, $index);
        } catch (UndefinedIndexException $e) {
            throw new UndefinedArgumentException($index, $e);
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
     * @param integer $index The index.
     *
     * @return boolean True if the argument exists.
     */
    public function has($index = 0)
    {
        if ($this->indexNormalizer->tryNormalize($this->count, $index)) {
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
