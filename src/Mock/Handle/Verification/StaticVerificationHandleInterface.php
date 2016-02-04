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

use Eloquent\Phony\Mock\Handle\StaticHandleInterface;

/**
 * The interface used to identify static verification handles.
 *
 * @api
 */
interface StaticVerificationHandleInterface extends StaticHandleInterface,
    VerificationHandleInterface
{
}
