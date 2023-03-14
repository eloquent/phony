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
        $this->positional = [];
        $this->named = [];

        foreach ($arguments as $indexOrName => &$value) {
            if (is_string($indexOrName)) {
                $this->named[$indexOrName] = &$value;
            } else {
                $this->positional[] = &$value;
            }
        }
    }

    /**
     * Copy these arguments, breaking any references.
     *
     * @return Arguments The copied arguments.
     */
    public function copy(): self
    {
        $arguments = [];

        foreach ($this->arguments as $indexOrName => $argument) {
            $arguments[$indexOrName] = $argument;
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
     * Set an argument by index or name.
     *
     * If called with no arguments, sets the first positional argument to null.
     *
     * If called with one argument, sets the first positional argument to
     * `$indexOrNameOrValue`.
     *
     * If called with two arguments, sets the argument at `$indexOrNameOrValue`
     * to `$value`.
     *
     * @param mixed $indexOrNameOrValue The index, or name; or value, if no index or name is specified.
     * @param mixed $value              The value.
     *
     * @return $this                      This arguments object.
     * @throws UndefinedArgumentException If the requested argument is undefined.
     */
    public function set($indexOrNameOrValue = null, $value = null): self
    {
        if (func_num_args() > 1) {
            /** @var int|string $indexOrName */
            $indexOrName = $indexOrNameOrValue;
            /** @var null $value */
            $isNamed = is_string($indexOrName);
        } else {
            $indexOrName = 0;
            $normalized = 0;
            $value = $indexOrNameOrValue;
            $isNamed = false;
        }

        if ($isNamed) {
            /** @var string $indexOrName */
            if (!array_key_exists($indexOrName, $this->named)) {
                throw new UndefinedNamedArgumentException($indexOrName);
            }

            $this->named[$indexOrName] = $value;

            return $this;
        }

        /** @var int $indexOrName */

        if (!$this->normalizeIndex($this->count, $indexOrName, $normalized)) {
            throw new UndefinedPositionalArgumentException($indexOrName);
        }

        /** @var int $normalized */
        $this->arguments[$normalized] = $value;

        return $this;
    }

    /**
     * Returns true if the argument index or name exists.
     *
     * Negative indices are offset from the end of the list. That is, `-1`
     * indicates the last element, and `-2` indicates the second last element.
     *
     * @param int|string $indexOrName The index or name.
     *
     * @return bool True if the argument exists.
     */
    public function has(int|string $indexOrName = 0): bool
    {
        if (is_string($indexOrName)) {
            return array_key_exists($indexOrName, $this->named);
        }

        if ($this->normalizeIndex($this->count, $indexOrName)) {
            return true;
        }

        return false;
    }

    /**
     * Get an argument by index or name.
     *
     * Negative indices are offset from the end of the list. That is, `-1`
     * indicates the last element, and `-2` indicates the second last element.
     *
     * @param int|string $indexOrName The index or name.
     *
     * @return mixed                      The argument.
     * @throws UndefinedArgumentException If the requested argument is undefined.
     */
    public function get(int|string $indexOrName = 0)
    {
        if (is_string($indexOrName)) {
            if (!array_key_exists($indexOrName, $this->named)) {
                throw new UndefinedNamedArgumentException($indexOrName);
            }

            return $this->arguments[$indexOrName];
        }

        if (!$this->normalizeIndex($this->count, $indexOrName, $normalized)) {
            throw new UndefinedPositionalArgumentException($indexOrName);
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
     * @var int
     */
    private $count;
}
