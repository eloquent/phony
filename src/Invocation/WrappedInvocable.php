<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2017 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Invocation;

/**
 * The interface implemented by wrapped invocables.
 */
interface WrappedInvocable extends Invocable
{
    /**
     * Set the label.
     *
     * @param string $label The label.
     *
     * @return $this This invocable.
     */
    public function setLabel(string $label): self;

    /**
     * Get the label.
     *
     * @return string The label.
     */
    public function label(): string;

    /**
     * Returns true if anonymous.
     *
     * @return bool True if anonymous.
     */
    public function isAnonymous(): bool;

    /**
     * Get the callback.
     *
     * @return callable The callback.
     */
    public function callback();
}
