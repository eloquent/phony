<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Invocation;

/**
 * An abstract base class for implementing wrapped invocables.
 *
 * @internal
 */
abstract class AbstractWrappedInvocable extends AbstractInvocable implements
    WrappedInvocableInterface
{
    /**
     * Construct a new wrapped invocable.
     *
     * @param callable|null $callback The callback.
     * @param string|null   $label    The label.
     */
    public function __construct($callback = null, $label = null)
    {
        if (null === $callback) {
            $this->isAnonymous = true;
            $this->callback = function () {};
        } else {
            $this->isAnonymous = false;
            $this->callback = $callback;
        }

        $this->label = $label;
    }

    /**
     * Returns true if anonymous.
     *
     * @return boolean True if anonymous.
     */
    public function isAnonymous()
    {
        return $this->isAnonymous;
    }

    /**
     * Get the callback.
     *
     * @return callable The callback.
     */
    public function callback()
    {
        return $this->callback;
    }

    /**
     * Set the label.
     *
     * @param string|null $label The label.
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * Get the label.
     *
     * @return string|null The label.
     */
    public function label()
    {
        return $this->label;
    }

    protected $isAnonymous;
    protected $callback;
    protected $label;
}
