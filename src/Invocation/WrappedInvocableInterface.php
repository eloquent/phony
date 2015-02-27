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
 * The interface implemented by wrapped invocables.
 */
interface WrappedInvocableInterface extends InvocableInterface
{
    /**
     * Returns true if anonymous.
     *
     * @return boolean True if anonymous.
     */
    public function isAnonymous();

    /**
     * Get the callback.
     *
     * @return callable The callback.
     */
    public function callback();

    /**
     * Set the label.
     *
     * @param string|null $label The label.
     */
    public function setLabel($label);

    /**
     * Get the label.
     *
     * @return string|null The label.
     */
    public function label();
}
