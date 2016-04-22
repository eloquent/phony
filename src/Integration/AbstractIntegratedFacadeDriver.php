<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Integration;

use Eloquent\Phony\Assertion\AssertionRecorder;
use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Call\Factory\CallVerifierFactory;
use Eloquent\Phony\Event\Verification\EventOrderVerifier;
use Eloquent\Phony\Exporter\InlineExporter;
use Eloquent\Phony\Facade\FacadeDriver;
use Eloquent\Phony\Feature\FeatureDetector;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Mock\Builder\Factory\MockBuilderFactory;
use Eloquent\Phony\Mock\Factory\MockFactory;
use Eloquent\Phony\Mock\Generator\MockGenerator;
use Eloquent\Phony\Mock\Handle\Factory\HandleFactory;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Spy\Factory\SpyFactory;
use Eloquent\Phony\Spy\Factory\SpyVerifierFactory;
use Eloquent\Phony\Stub\Answer\Builder\Factory\GeneratorAnswerBuilderFactory;
use Eloquent\Phony\Stub\Factory\StubFactory;
use Eloquent\Phony\Stub\Factory\StubVerifierFactory;

/**
 * An abstract base class for implementing facade drivers that integrate with
 * third party testing frameworks.
 */
abstract class AbstractIntegratedFacadeDriver extends FacadeDriver
{
    /**
     * Construct a new integrated facade driver.
     */
    public function __construct()
    {
        $assertionRecorder = $this->createAssertionRecorder();
        $assertionRenderer = AssertionRenderer::instance();
        $matcherFactory = MatcherFactory::instance();
        $matcherVerifier = MatcherVerifier::instance();
        $invocableInspector = InvocableInspector::instance();
        $callVerifierFactory = new CallVerifierFactory(
            $matcherFactory,
            $matcherVerifier,
            $assertionRecorder,
            $assertionRenderer,
            $invocableInspector
        );
        $spyFactory = SpyFactory::instance();
        $stubFactory = StubFactory::instance();
        $invoker = Invoker::instance();
        $stubVerifierFactory = new StubVerifierFactory(
            $stubFactory,
            $spyFactory,
            $matcherFactory,
            $matcherVerifier,
            $callVerifierFactory,
            $assertionRecorder,
            $assertionRenderer,
            $invocableInspector,
            $invoker,
            GeneratorAnswerBuilderFactory::instance()
        );
        $handleFactory = new HandleFactory(
            $stubFactory,
            $stubVerifierFactory,
            $assertionRenderer,
            $assertionRecorder,
            $invoker
        );

        parent::__construct(
            new MockBuilderFactory(
                new MockFactory(
                    Sequencer::sequence('mock-label'),
                    MockGenerator::instance(),
                    $handleFactory
                ),
                $handleFactory,
                $invocableInspector,
                FeatureDetector::instance()
            ),
            $handleFactory,
            new SpyVerifierFactory(
                $spyFactory,
                $matcherFactory,
                $matcherVerifier,
                $callVerifierFactory,
                $assertionRecorder,
                $assertionRenderer,
                $invocableInspector
            ),
            $stubVerifierFactory,
            new EventOrderVerifier($assertionRecorder, $assertionRenderer),
            $matcherFactory,
            InlineExporter::instance()
        );
    }

    /**
     * Create the assertion recorder.
     *
     * @return AssertionRecorder The assertion recorder.
     */
    abstract protected function createAssertionRecorder();
}
