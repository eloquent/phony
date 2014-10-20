<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Proxy\Verification;

use Eloquent\Phony\Mock\MockInterface;
use Eloquent\Phony\Stub\StubVerifierInterface;

/**
 * A proxy for verifying a mock.
 *
 * @internal
 */
class VerificationProxy extends AbstractVerificationProxy implements
    InstanceVerificationProxyInterface
{
    /**
     * Construct a new verification proxy.
     *
     * @param MockInterface                       $mock  The mock.
     * @param array<string,StubVerifierInterface> $stubs The stubs.
     */
    public function __construct(MockInterface $mock, array $stubs)
    {
        parent::__construct(get_class($mock), $stubs);

        $this->mock = $mock;
    }

    /**
     * Get the mock.
     *
     * @return MockInterface The mock.
     */
    public function mock()
    {
        return $this->mock;
    }

    private $mock;
}
