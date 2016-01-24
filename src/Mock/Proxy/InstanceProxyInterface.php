<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Proxy;

use Eloquent\Phony\Call\Argument\ArgumentsInterface;
use Eloquent\Phony\Mock\MockInterface;

/**
 * The interface implemented by instance proxies.
 *
 * @api
 */
interface InstanceProxyInterface extends ProxyInterface
{
    /**
     * Get the mock.
     *
     * @api
     *
     * @return MockInterface The mock.
     */
    public function mock();

    /**
     * Call the original constructor.
     *
     * @api
     *
     * @param mixed ...$arguments The arguments.
     *
     * @return $this This proxy.
     */
    public function construct();

    /**
     * Call the original constructor.
     *
     * @api
     *
     * @param ArgumentsInterface|array $arguments The arguments.
     *
     * @return $this This proxy.
     */
    public function constructWith($arguments = array());

    /**
     * Set the label.
     *
     * @api
     *
     * @param string|null $label The label.
     *
     * @return $this This proxy.
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
     * Set whether this proxy should be adapted to its mock automatically.
     *
     * @param boolean $isAdaptable True if this proxy should be adapted automatically.
     *
     * @return $this This proxy.
     */
    public function setIsAdaptable($isAdaptable);

    /**
     * Returns true if this proxy should be adapted to its mock automatically.
     *
     * @return boolean True if this proxy should be adapted automatically.
     */
    public function isAdaptable();
}
