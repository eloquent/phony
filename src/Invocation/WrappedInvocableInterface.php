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
 *
 * @api
 */
interface WrappedInvocableInterface extends InvocableInterface
{
    /**
     * Set the label.
     *
     * @api
     *
     * @param string|null $label The label.
     *
     * @return $this This invocable.
     */
    public function setLabel($label);

    /**
     * Get the label.
     *
     * @api
     *
     * @return string|null The label.
     */
    public function label();

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
}
