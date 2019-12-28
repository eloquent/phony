<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call;

use ArrayIterator;
use Countable;
use Eloquent\Phony\Call\Exception\UndefinedArgumentException;
use Eloquent\Phony\Collection\NormalizesIndices;
use Iterator;
use IteratorAggregate;

/**
 * Represents a set of call arguments.
 *
 * @implements IteratorAggregate<int,mixed>
 */
class Arguments implements Countable, IteratorAggregate
{
    use NormalizesIndices;

    /**
     * Create a new set of call arguments from the supplied arguments.
     *
     * @param mixed ...$arguments The arguments.
     *
     * @return Arguments The arguments object.
     */
    public static function create(...$arguments): self
    {
        return new self($arguments);
    }

    /**
     * Construct a new set of call arguments.
     *
     * @param array<int,mixed> $arguments The arguments.
     */
    public function __construct(array $arguments)
    {
        $this->arguments = $arguments;
        $this->count = count($arguments);
    }

    /**
     * Copy these arguments, breaking any references.
     *
     * @return Arguments The copied arguments.
     */
    public function copy(): self
    {
        $arguments = [];

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
     * @return array<int,mixed> The arguments.
     */
    public function all(): array
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
    public function set($indexOrValue = null, $value = null): self
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
     * @param int $index The index.
     *
     * @return bool True if the argument exists.
     */
    public function has(int $index = 0): bool
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
     * @param int $index The index.
     *
     * @return mixed                      The argument.
     * @throws UndefinedArgumentException If the requested argument is undefined.
     */
    public function get(int $index = 0)
    {
        if (!$this->normalizeIndex($this->count, $index, $normalized)) {
            throw new UndefinedArgumentException($index);
        }

        return $this->arguments[$normalized];
    }

    /**
     * Get the number of arguments.
     *
     * @return int The number of arguments.
     */
    public function count(): int
    {
        return $this->count;
    }

    /**
     * Get an iterator for these arguments.
     *
     * @return Iterator<int,mixed> The iterator.
     */
    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->arguments);
    }

    /**
     * @var array<int,mixed>
     */
    private $arguments;

    /**
     * @var int
     */
    private $count;
}
