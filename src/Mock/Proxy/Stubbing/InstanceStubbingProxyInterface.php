<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Proxy\Stubbing;

use Eloquent\Phony\Mock\Proxy\InstanceProxyInterface;

/**
 * The interface implemented by instance stubbing proxies.
 *
 * @api
 */
interface InstanceStubbingProxyInterface extends InstanceProxyInterface,
    StubbingProxyInterface
{
}
