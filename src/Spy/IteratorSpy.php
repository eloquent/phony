<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy;

use Eloquent\Phony\Call\Call;
use Eloquent\Phony\Call\Event\CallEventFactory;
use Iterator;

/**
 * Spies on an iterator.
 */
class IteratorSpy implements Iterator
{
    /**
     * Construct a new iterator spy.
     *
     * @param Call             $call             The call from which the iterator originated.
     * @param Iterator         $iterator         The iterator.
     * @param CallEventFactory $callEventFactory The call event factory to use.
     */
    public function __construct(
        Call $call,
        Iterator $iterator,
        CallEventFactory $callEventFactory
    ) {
        $this->call = $call;
        $this->iterator = $iterator;
        $this->callEventFactory = $callEventFactory;
        $this->isConsumed = false;
    }

    /**
     * Get the call.
     *
     * @return Call The call.
     */
    public function call()
    {
        return $this->call;
    }

    /**
     * Get the iterator.
     *
     * @return Iterator The iterator.
     */
    public function iterator()
    {
        return $this->iterator;
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
    public function next()
    {
        $this->iterator->next();
    }

    /**
     * Rewind the iterator.
     */
    public function rewind()
    {
        $this->iterator->rewind();
    }

    /**
     * Returns true if the current iterator position is valid.
     *
     * @return boolean True if the current iterator position is valid.
     */
    public function valid()
    {
        if ($isValid = $this->iterator->valid()) {
            $this->key = $this->iterator->key();
            $this->value = $this->iterator->current();
        } else {
            $this->key = null;
            $this->value = null;
        }

        if ($this->isConsumed) {
            return $isValid;
        }

        if ($isValid) {
            $this->call->addTraversableEvent(
                $this->callEventFactory
                    ->createProduced($this->key, $this->value)
            );
        } else {
            $this->call->setEndEvent($this->callEventFactory->createConsumed());
            $this->isConsumed = true;
        }

        return $isValid;
    }

    private $call;
    private $iterator;
    private $callEventFactory;
    private $key;
    private $value;
    private $isConsumed;
}
