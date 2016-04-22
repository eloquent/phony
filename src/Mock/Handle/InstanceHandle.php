<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Handle;

use Eloquent\Phony\Call\Argument\Arguments;
use Eloquent\Phony\Mock\Mock;

/**
 * The interface implemented by instance handles.
 *
 * @api
 */
interface InstanceHandle extends Handle
{
    /**
     * Get the mock.
     *
     * @api
     *
     * @return Mock The mock.
     */
    public function mock();

    /**
     * Call the original constructor.
     *
     * @api
     *
     * @param mixed ...$arguments The arguments.
     *
     * @return $this This handle.
     */
    public function construct();

    /**
     * Call the original constructor.
     *
     * @api
     *
     * @param Arguments|array $arguments The arguments.
     *
     * @return $this This handle.
     */
    public function constructWith($arguments = array());

    /**
     * Set the label.
     *
     * @api
     *
     * @param string|null $label The label.
     *
     * @return $this This handle.
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
     * Set whether this handle should be adapted to its mock automatically.
     *
     * @param boolean $isAdaptable True if this handle should be adapted automatically.
     *
     * @return $this This handle.
     */
    public function setIsAdaptable($isAdaptable);

    /**
     * Returns true if this handle should be adapted to its mock automatically.
     *
     * @return boolean True if this handle should be adapted automatically.
     */
    public function isAdaptable();
}
