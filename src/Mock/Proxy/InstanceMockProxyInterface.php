<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Proxy;

use Eloquent\Phony\Mock\MockInterface;

/**
 * The interface implemented by instance mock proxies.
 */
interface InstanceMockProxyInterface extends MockProxyInterface
{
    /**
     * Get the mock.
     *
     * @return MockInterface The mock.
     */
    public function mock();
}
