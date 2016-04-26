<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Facade;

use Eloquent\Phony\Event\EventOrderVerifier;
use Eloquent\Phony\Exporter\Exporter;
use Eloquent\Phony\Exporter\InlineExporter;
use Eloquent\Phony\Matcher\MatcherFactory;
use Eloquent\Phony\Mock\Builder\MockBuilderFactory;
use Eloquent\Phony\Mock\Handle\HandleFactory;
use Eloquent\Phony\Spy\SpyVerifierFactory;
use Eloquent\Phony\Stub\StubVerifierFactory;

/**
 * A service container that supplies all of the underlying services required by
 * the facades.
 */
class FacadeDriver
{
    /**
     * Get the static instance of this driver.
     *
     * @return FacadeDriver The static driver.
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self(
                MockBuilderFactory::instance(),
                HandleFactory::instance(),
                SpyVerifierFactory::instance(),
                StubVerifierFactory::instance(),
                EventOrderVerifier::instance(),
                MatcherFactory::instance(),
                InlineExporter::instance()
            );
        }

        return self::$instance;
    }

    /**
     * Construct a new facade driver.
     *
     * @param MockBuilderFactory  $mockBuilderFactory  The mock builder factory to use.
     * @param HandleFactory       $handleFactory       The handle factory to use.
     * @param SpyVerifierFactory  $spyVerifierFactory  The spy verifier factory to use.
     * @param StubVerifierFactory $stubVerifierFactory The stub verifier factory to use.
     * @param EventOrderVerifier  $eventOrderVerifier  The event order verifier to use.
     * @param MatcherFactory      $matcherFactory      The matcher factory to use.
     * @param Exporter            $exporter            The exporter to use.
     */
    public function __construct(
        MockBuilderFactory $mockBuilderFactory,
        HandleFactory $handleFactory,
        SpyVerifierFactory $spyVerifierFactory,
        StubVerifierFactory $stubVerifierFactory,
        EventOrderVerifier $eventOrderVerifier,
        MatcherFactory $matcherFactory,
        Exporter $exporter
    ) {
        $this->mockBuilderFactory = $mockBuilderFactory;
        $this->handleFactory = $handleFactory;
        $this->spyVerifierFactory = $spyVerifierFactory;
        $this->stubVerifierFactory = $stubVerifierFactory;
        $this->eventOrderVerifier = $eventOrderVerifier;
        $this->matcherFactory = $matcherFactory;
        $this->exporter = $exporter;
    }

    public $mockBuilderFactory;
    public $handleFactory;
    public $spyVerifierFactory;
    public $stubVerifierFactory;
    public $eventOrderVerifier;
    public $matcherFactory;
    public $exporter;
    private static $instance;
}
