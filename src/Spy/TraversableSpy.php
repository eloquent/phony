<?php

declare(strict_types=1);

namespace Eloquent\Phony\Spy;

use ArrayAccess;
use Countable;
use Eloquent\Phony\Call\Call;
use Eloquent\Phony\Call\Event\CallEventFactory;
use Eloquent\Phony\Spy\Exception\NonArrayAccessTraversableException;
use Eloquent\Phony\Spy\Exception\NonCountableTraversableException;
use Iterator;
use IteratorAggregate;
use Traversable;

/**
 * Spies on an traversable.
 */
class TraversableSpy implements IterableSpy
{
    /**
     * Construct a new traversable spy.
     *
     * @param Call               $call             The call from which the array originated.
     * @param Traversable<mixed> $traversable      The traversable.
     * @param CallEventFactory   $callEventFactory The call event factory to use.
     */
    public function __construct(
        Call $call,
        Traversable $traversable,
        CallEventFactory $callEventFactory
    ) {
        $this->call = $call;
        $this->traversable = $traversable;
        $this->callEventFactory = $callEventFactory;
        $this->isUsed = false;
        $this->isConsumed = false;
    }

    /**
     * Get the original iterable value.
     *
     * @return iterable<mixed> The original value.
     */
    public function iterable(): iterable
    {
        return $this->traversable;
    }

    /**
     * Get the current key.
     *
     * @return mixed The current key.
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * Get the current value.
     *
     * @return mixed The current value.
     */
    public function current()
    {
        return $this->value;
    }

    /**
     * Move the current position to the next element.
     */
    public function next(): void
    {
        if ($this->iterator) {
            $this->iterator->next();
        }
    }

    /**
     * Rewind the iterator.
     */
    public function rewind(): void
    {
        if ($this->iterator) {
            $this->iterator->rewind();
        }
    }

    /**
     * Returns true if the current iterator position is valid.
     *
     * @return bool True if the current iterator position is valid.
     */
    public function valid(): bool
    {
        if (!$this->isUsed) {
            $this->call
                ->addIterableEvent($this->callEventFactory->createUsed());

            if ($this->traversable instanceof IteratorAggregate) {
                $iterator = $this->traversable->getIterator();

                while (!$iterator instanceof Iterator) {
                    $iterator = $iterator->getIterator();
                }
            } else {
                $iterator = $this->traversable;
            }

            $this->iterator = $iterator;
            $this->isUsed = true;
        }

        /** @var Iterator<mixed> */
        $iterator = $this->iterator;

        if ($isValid = $iterator->valid()) {
            $this->key = $iterator->key();
            $this->value = $iterator->current();
        } else {
            $this->key = null;
            $this->value = null;
        }

        if ($this->isConsumed) {
            return $isValid;
        }

        if ($isValid) {
            $this->call->addIterableEvent(
                $this->callEventFactory
                    ->createProduced($this->key, $this->value)
            );
        } else {
            $this->call->setEndEvent($this->callEventFactory->createConsumed());
            $this->isConsumed = true;
        }

        return $isValid;
    }

    /**
     * Check if a key exists.
     *
     * @param mixed $key The key.
     *
     * @return bool True if the key exists.
     */
    public function offsetExists($key): bool
    {
        if (!$this->traversable instanceof ArrayAccess) {
            throw new NonArrayAccessTraversableException($this->traversable);
        }

        /** @var ArrayAccess<mixed,mixed> */
        $arrayAccess = $this->traversable;

        return isset($arrayAccess[$key]);
    }

    /**
     * Get a value.
     *
     * @param mixed $key The key.
     *
     * @return mixed The value.
     */
    public function offsetGet($key)
    {
        if (!$this->traversable instanceof ArrayAccess) {
            throw new NonArrayAccessTraversableException($this->traversable);
        }

        /** @var ArrayAccess<mixed,mixed> */
        $arrayAccess = $this->traversable;

        return $arrayAccess[$key];
    }

    /**
     * Set a value.
     *
     * @param mixed $key   The key.
     * @param mixed $value The value.
     */
    public function offsetSet($key, $value): void
    {
        if (!$this->traversable instanceof ArrayAccess) {
            throw new NonArrayAccessTraversableException($this->traversable);
        }

        $this->traversable[$key] = $value;
    }

    /**
     * Un-set a value.
     *
     * @param mixed $key The key.
     */
    public function offsetUnset($key): void
    {
        if (!$this->traversable instanceof ArrayAccess) {
            throw new NonArrayAccessTraversableException($this->traversable);
        }

        /** @var ArrayAccess<mixed,mixed> */
        $arrayAccess = $this->traversable;

        unset($arrayAccess[$key]);
    }

    /**
     * Get the count.
     *
     * @return int                              The count.
     * @throws NonCountableTraversableException If the traversable does not implement Countable.
     */
    public function count(): int
    {
        if (!$this->traversable instanceof Countable) {
            throw new NonCountableTraversableException($this->traversable);
        }

        return count($this->traversable);
    }

    /**
     * @var Call
     */
    private $call;

    /**
     * @var Traversable<mixed>
     */
    private $traversable;

    /**
     * @var CallEventFactory
     */
    private $callEventFactory;

    /**
     * @var bool
     */
    private $isUsed;

    /**
     * @var bool
     */
    private $isConsumed;

    /**
     * @var ?Iterator<mixed>
     */
    private $iterator;

    /**
     * @var mixed
     */
    private $key;

    /**
     * @var mixed
     */
    private $value;
}
