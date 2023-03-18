<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call;

use ArrayIterator;
use Countable;
use Eloquent\Phony\Call\Exception\UndefinedArgumentException;
use Eloquent\Phony\Call\Exception\UndefinedNamedArgumentException;
use Eloquent\Phony\Call\Exception\UndefinedPositionalArgumentException;
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
     * @param array<int|string,mixed> $arguments The arguments.
     */
    public function __construct(array $arguments)
    {
        $this->arguments = $arguments;
        $this->count = count($arguments);
        $this->positionalCount = 0;
        $this->positional = [];
        $this->named = [];

        $firstPositionOrName = 0;
        $isFirst = true;

        foreach ($arguments as $positionOrName => &$value) {
            if ($isFirst) {
                $isFirst = false;
                $firstPositionOrName = $positionOrName;
            }

            if (is_string($positionOrName)) {
                $this->named[$positionOrName] = &$value;
            } else {
                ++$this->positionalCount;
                $this->positional[] = &$value;
            }
        }

        $this->firstPositionOrName = $firstPositionOrName;
    }

    /**
     * Copy these arguments, breaking any references.
     *
     * @return Arguments The copied arguments.
     */
    public function copy(): self
    {
        $arguments = [];

        foreach ($this->arguments as $positionOrName => $argument) {
            $arguments[$positionOrName] = $argument;
        }

        return new self($arguments);
    }

    /**
     * Get the arguments.
     *
     * This method supports reference parameters.
     *
     * @return array<int|string,mixed> The arguments.
     */
    public function all(): array
    {
        return $this->arguments;
    }

    /**
     * Get the positional arguments.
     *
     * This method supports reference parameters.
     *
     * @return array<int,mixed> The positional arguments.
     */
    public function positional(): array
    {
        return $this->positional;
    }

    /**
     * Get the named arguments.
     *
     * This method supports reference parameters.
     *
     * @return array<string,mixed> The named arguments.
     */
    public function named(): array
    {
        return $this->named;
    }

    /**
     * Set an argument by position or name.
     *
     * If called with no arguments, sets the first argument to null.
     *
     * If called with one argument, sets the first argument to
     * `$positionOrNameOrValue`.
     *
     * If called with two arguments, sets the argument at
     * `$positionOrNameOrValue` to `$value`.
     *
     * Negative positions are offset from the end of the positional arguments.
     * That is, `-1` indicates the last positional argument, and `-2` indicates
     * the second-to-last positional argument.
     *
     * @param mixed $positionOrNameOrValue The position, or name; or value, if no position or name is specified.
     * @param mixed $value                 The value.
     *
     * @return $this                      This arguments object.
     * @throws UndefinedArgumentException If the requested argument is undefined.
     */
    public function set($positionOrNameOrValue = null, $value = null): self
    {
        if (func_num_args() > 1) {
            /** @var int|string $positionOrName */
            $positionOrName = $positionOrNameOrValue;
            /** @var null $value */
        } else {
            $positionOrName = $this->firstPositionOrName;
            $normalized = $this->firstPositionOrName;
            $value = $positionOrNameOrValue;
        }

        if (is_string($positionOrName)) {
            /** @var string $positionOrName */
            if (!array_key_exists($positionOrName, $this->named)) {
                throw new UndefinedNamedArgumentException($positionOrName);
            }

            $this->named[$positionOrName] = $value;

            return $this;
        }

        /** @var int $positionOrName */

        if (
            !$this->normalizeIndex(
                $this->positionalCount,
                $positionOrName,
                $normalized
            )
        ) {
            throw new UndefinedPositionalArgumentException($positionOrName);
        }

        /** @var int $normalized */
        $this->arguments[$normalized] = $value;

        return $this;
    }

    /**
     * Returns true if the argument position or name exists.
     *
     * Negative positions are offset from the end of the positional arguments.
     * That is, `-1` indicates the last positional argument, and `-2` indicates
     * the second-to-last positional argument.
     *
     * @param int|string $positionOrName The position or name.
     *
     * @return bool True if the argument exists.
     */
    public function has(int|string $positionOrName = 0): bool
    {
        if (is_string($positionOrName)) {
            return array_key_exists($positionOrName, $this->named);
        }

        if ($this->normalizeIndex($this->positionalCount, $positionOrName)) {
            return true;
        }

        return false;
    }

    /**
     * Get an argument by position or name.
     *
     * Negative positions are offset from the end of the positional arguments.
     * That is, `-1` indicates the last positional argument, and `-2` indicates
     * the second-to-last positional argument.
     *
     * @param int|string $positionOrName The position or name.
     *
     * @return mixed                      The argument.
     * @throws UndefinedArgumentException If the requested argument is undefined.
     */
    public function get(int|string $positionOrName = 0)
    {
        if (is_string($positionOrName)) {
            if (!array_key_exists($positionOrName, $this->named)) {
                throw new UndefinedNamedArgumentException($positionOrName);
            }

            return $this->arguments[$positionOrName];
        }

        if (
            !$this->normalizeIndex(
                $this->positionalCount,
                $positionOrName,
                $normalized
            )
        ) {
            throw new UndefinedPositionalArgumentException($positionOrName);
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
     * @return Iterator<int|string,mixed> The iterator.
     */
    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->arguments);
    }

    /**
     * @var array<int|string,mixed>
     */
    private $arguments;

    /**
     * @var array<int,mixed>
     */
    private $positional;

    /**
     * @var array<string,mixed>
     */
    private $named;

    /**
     * @var int|string
     */
    private $firstPositionOrName;

    /**
     * @var int
     */
    private $count;

    /**
     * @var int
     */
    private $positionalCount;
}
