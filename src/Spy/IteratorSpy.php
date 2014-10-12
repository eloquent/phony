<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy;

use Eloquent\Phony\Call\CallInterface;
use Eloquent\Phony\Call\Event\Factory\CallEventFactory;
use Eloquent\Phony\Call\Event\Factory\CallEventFactoryInterface;
use Iterator;

/**
 * Spies on an iterator.
 *
 * @internal
 */
class IteratorSpy implements IteratorSpyInterface
{
    /**
     * Construct a new iterator spy.
     *
     * @param CallInterface                  $call             The call from which the iterator originated.
     * @param Iterator                       $iterator         The iterator.
     * @param CallEventFactoryInterface|null $callEventFactory The call event factory to use.
     */
    public function __construct(
        CallInterface $call,
        Iterator $iterator,
        CallEventFactoryInterface $callEventFactory = null
    ) {
        if (null === $callEventFactory) {
            $callEventFactory = CallEventFactory::instance();
        }

        $this->call = $call;
        $this->iterator = $iterator;
        $this->callEventFactory = $callEventFactory;
    }

    /**
     * Get the call.
     *
     * @return CallInterface The call.
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
     * Get the call event factory.
     *
     * @return CallEventFactoryInterface The call event factory.
     */
    public function callEventFactory()
    {
        return $this->callEventFactory;
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

            $this->call->addTraversableEvent(
                $this->callEventFactory
                    ->createProduced($this->key, $this->value)
            );
        } else {
            $this->value = null;
            $this->key = null;
        }

        return $isValid;
    }

    private $call;
    private $iterator;
    private $callEventFactory;
    private $key;
    private $value;
}
