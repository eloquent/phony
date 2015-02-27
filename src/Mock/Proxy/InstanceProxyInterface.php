<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Proxy;

use Eloquent\Phony\Call\Argument\ArgumentsInterface;
use Eloquent\Phony\Mock\MockInterface;

/**
 * The interface implemented by instance proxies.
 */
interface InstanceProxyInterface extends ProxyInterface
{
    /**
     * Get the mock.
     *
     * @return MockInterface The mock.
     */
    public function mock();

    /**
     * Call the original constructor.
     *
     * @param mixed $arguments,... The arguments.
     *
     * @return ProxyInterface This proxy.
     */
    public function construct();

    /**
     * Call the original constructor.
     *
     * @param ArgumentsInterface|array<integer,mixed>|null $arguments The arguments.
     *
     * @return ProxyInterface This proxy.
     */
    public function constructWith($arguments = null);

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
