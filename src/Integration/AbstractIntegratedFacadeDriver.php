<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Integration;

use Eloquent\Phony\Assertion\Recorder\AssertionRecorderInterface;
use Eloquent\Phony\Call\Factory\CallVerifierFactory;
use Eloquent\Phony\Event\Verification\EventOrderVerifier;
use Eloquent\Phony\Facade\FacadeDriver;
use Eloquent\Phony\Mock\Builder\Factory\MockBuilderFactory;
use Eloquent\Phony\Mock\Factory\MockFactory;
use Eloquent\Phony\Mock\Proxy\Factory\ProxyFactory;
use Eloquent\Phony\Spy\Factory\SpyVerifierFactory;
use Eloquent\Phony\Stub\Factory\StubVerifierFactory;

/**
 * An abstract base class for implementing facade drivers that integrate with
 * third party testing frameworks.
 *
 * @internal
 */
abstract class AbstractIntegratedFacadeDriver extends FacadeDriver
{
    /**
     * Construct a new PHPUnit facade driver.
     */
    public function __construct()
    {
        $assertionRecorder = $this->createAssertionRecorder();
        $callVerifierFactory =
            new CallVerifierFactory(null, null, $assertionRecorder);
        $stubVerifierFactory = new StubVerifierFactory(
            null,
            null,
            null,
            null,
            $callVerifierFactory,
            $assertionRecorder
        );
        $proxyFactory = new ProxyFactory(null, $stubVerifierFactory);

        parent::__construct(
            new MockBuilderFactory(new MockFactory(null, null, $proxyFactory)),
            $proxyFactory,
            new SpyVerifierFactory(
                null,
                null,
                null,
                $callVerifierFactory,
                $assertionRecorder
            ),
            $stubVerifierFactory,
            new EventOrderVerifier($assertionRecorder)
        );
    }

    /**
     * Create the assertion recorder.
     *
     * @return AssertionRecorderInterface The assertion recorder.
     */
    abstract protected function createAssertionRecorder();
}
