<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Integration\Phpunit;

use Eloquent\Phony\Call\Factory\CallVerifierFactory;
use Eloquent\Phony\Event\Verification\EventOrderVerifier;
use Eloquent\Phony\Facade\FacadeDriver;
use Eloquent\Phony\Facade\FacadeDriverInterface;
use Eloquent\Phony\Mock\Proxy\Factory\MockProxyFactory;
use Eloquent\Phony\Spy\Factory\SpyVerifierFactory;
use Eloquent\Phony\Stub\Factory\StubVerifierFactory;

/**
 * A facade driver for PHPUnit.
 *
 * @internal
 */
class PhpunitFacadeDriver extends FacadeDriver
{
    /**
     * Get the static instance of this driver.
     *
     * @return FacadeDriverInterface The static driver.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Construct a new PHPUnit facade driver.
     */
    public function __construct()
    {
        $assertionRecorder = PhpunitAssertionRecorder::instance();
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

        parent::__construct(
            null,
            new MockProxyFactory($stubVerifierFactory),
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

    private static $instance;
}
