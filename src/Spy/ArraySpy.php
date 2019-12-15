<?php

declare(strict_types=1);

namespace Eloquent\Phony\Spy;

use Eloquent\Phony\Call\Call;
use Eloquent\Phony\Call\Event\CallEventFactory;

/**
 * Spies on an array.
 */
class ArraySpy implements IterableSpy
{
    /**
     * Construct a new array spy.
     *
     * @param Call             $call             The call from which the array originated.
     * @param array<mixed>     $array            The array.
     * @param CallEventFactory $callEventFactory The call event factory to use.
     */
    public function __construct(
        Call $call,
        array $array,
        CallEventFactory $callEventFactory
    ) {
        $this->call = $call;
        $this->array = $array;
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
        return $this->array;
    }

    /**
     * Get the current key.
     *
     * @return mixed The current key.
     */
    public function key()
    {
        return key($this->array);
    }

    /**
     * Get the current value.
     *
     * @return mixed The current value.
     */
    public function current()
    {
        return current($this->array);
    }

    /**
     * Move the current position to the next element.
     */
    public function next(): void
    {
        next($this->array);
    }

    /**
     * Rewind the iterator.
     */
    public function rewind(): void
    {
        reset($this->array);
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
            $this->isUsed = true;
        }

        $key = key($this->array);
        $isValid = null !== $key;

        if ($this->isConsumed) {
            return $isValid;
        }

        if ($isValid) {
            $this->call->addIterableEvent(
                $this->callEventFactory
                    ->createProduced($key, current($this->array))
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
        return isset($this->array[$key]);
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
        return $this->array[$key];
    }

    /**
     * Set a value.
     *
     * @param mixed $key   The key.
     * @param mixed $value The value.
     */
    public function offsetSet($key, $value): void
    {
        $this->array[$key] = $value;
    }

    /**
     * Un-set a value.
     *
     * @param mixed $key The key.
     */
    public function offsetUnset($key): void
    {
        unset($this->array[$key]);
    }

    /**
     * Get the count.
     *
     * @return int The count.
     */
    public function count(): int
    {
        return count($this->array);
    }

    /**
     * @var Call
     */
    private $call;

    /**
     * @var array<mixed>
     */
    private $array;

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
}
