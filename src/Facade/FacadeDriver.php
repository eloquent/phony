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

use Eloquent\Phony\Event\Verification\EventOrderVerifier;
use Eloquent\Phony\Event\Verification\EventOrderVerifierInterface;
use Eloquent\Phony\Exporter\ExporterInterface;
use Eloquent\Phony\Exporter\InlineExporter;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Factory\MatcherFactoryInterface;
use Eloquent\Phony\Mock\Builder\Factory\MockBuilderFactory;
use Eloquent\Phony\Mock\Builder\Factory\MockBuilderFactoryInterface;
use Eloquent\Phony\Mock\Handle\Factory\HandleFactory;
use Eloquent\Phony\Mock\Handle\Factory\HandleFactoryInterface;
use Eloquent\Phony\Spy\Factory\SpyVerifierFactory;
use Eloquent\Phony\Spy\Factory\SpyVerifierFactoryInterface;
use Eloquent\Phony\Stub\Factory\StubVerifierFactory;
use Eloquent\Phony\Stub\Factory\StubVerifierFactoryInterface;

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
     * @param MockBuilderFactoryInterface  $mockBuilderFactory  The mock builder factory to use.
     * @param HandleFactoryInterface       $handleFactory       The handle factory to use.
     * @param SpyVerifierFactoryInterface  $spyVerifierFactory  The spy verifier factory to use.
     * @param StubVerifierFactoryInterface $stubVerifierFactory The stub verifier factory to use.
     * @param EventOrderVerifierInterface  $eventOrderVerifier  The event order verifier to use.
     * @param MatcherFactoryInterface      $matcherFactory      The matcher factory to use.
     * @param ExporterInterface            $exporter            The exporter to use.
     */
    public function __construct(
        MockBuilderFactoryInterface $mockBuilderFactory,
        HandleFactoryInterface $handleFactory,
        SpyVerifierFactoryInterface $spyVerifierFactory,
        StubVerifierFactoryInterface $stubVerifierFactory,
        EventOrderVerifierInterface $eventOrderVerifier,
        MatcherFactoryInterface $matcherFactory,
        ExporterInterface $exporter
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
