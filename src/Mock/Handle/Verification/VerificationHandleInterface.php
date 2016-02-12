<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Handle\Verification;

use Eloquent\Phony\Mock\Exception\MockExceptionInterface;
use Eloquent\Phony\Mock\Handle\HandleInterface;
use Exception;

/**
 * The interface implemented by verification handles.
 *
 * @api
 */
interface VerificationHandleInterface extends HandleInterface
{
    /**
     * Throws an exception unless the specified method was called with the
     * supplied arguments.
     *
     * @api
     *
     * @param string $name      The method name.
     * @param array  $arguments The arguments.
     *
     * @return $this                  This handle.
     * @throws MockExceptionInterface If the stub does not exist.
     * @throws Exception              If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function __call($name, array $arguments);
}
