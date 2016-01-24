<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Proxy\Stubbing;

use Eloquent\Phony\Mock\Exception\MockExceptionInterface;
use Eloquent\Phony\Mock\Proxy\ProxyInterface;
use Eloquent\Phony\Stub\StubVerifierInterface;

/**
 * The interface implemented by stubbing proxies.
 *
 * @api
 */
interface StubbingProxyInterface extends ProxyInterface
{
    /**
     * Get a stub verifier, and modify its current criteria to match the
     * supplied arguments.
     *
     * @api
     *
     * @param string $name      The method name.
     * @param array  $arguments The arguments.
     *
     * @return StubVerifierInterface  The stub verifier.
     * @throws MockExceptionInterface If the stub does not exist.
     */
    public function __call($name, array $arguments);
}
