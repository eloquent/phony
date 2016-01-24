<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub;

use Eloquent\Phony\Spy\SpyVerifierInterface;

/**
 * The interface implemented by stub verifiers.
 *
 * @api
 */
interface StubVerifierInterface extends StubInterface, SpyVerifierInterface
{
}
