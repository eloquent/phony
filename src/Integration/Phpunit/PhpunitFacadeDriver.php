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
use Eloquent\Phony\Facade\FacadeDriver;
use Eloquent\Phony\Facade\FacadeDriverInterface;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Spy\Factory\SpyVerifierFactory;
use Eloquent\Phony\Stub\Factory\StubFactory;
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
        $matcherFactory =
            new MatcherFactory(array(PhpunitMatcherDriver::instance()));
        $assertionRecorder = PhpunitAssertionRecorder::instance();
        $callVerifierFactory =
            new CallVerifierFactory($matcherFactory, null, $assertionRecorder);

        parent::__construct(
            new SpyVerifierFactory(
                null,
                $matcherFactory,
                null,
                $callVerifierFactory,
                $assertionRecorder
            ),
            new StubVerifierFactory(
                new StubFactory($matcherFactory),
                null,
                $matcherFactory,
                null,
                $callVerifierFactory,
                $assertionRecorder
            )
        );
    }

    private static $instance;
}
