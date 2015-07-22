<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Proxy\Verification;

use Eloquent\Phony\Mock\Exception\MockExceptionInterface;
use Eloquent\Phony\Mock\Proxy\ProxyInterface;
use Exception;

/**
 * The interface implemented by verification proxies.
 */
interface VerificationProxyInterface extends ProxyInterface
{
    /**
     * Throws an exception unless the specified method was called with the
     * supplied arguments.
     *
     * @param string $name      The method name.
     * @param array  $arguments The arguments.
     *
     * @return VerificationProxyInterface This proxy.
     * @throws MockExceptionInterface     If the stub does not exist.
     * @throws Exception                  If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function __call($name, array $arguments);
}
