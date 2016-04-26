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
use Eloquent\Phony\Call\CallFactory;
use Eloquent\Phony\Call\CallVerifierFactory;
use Eloquent\Phony\Call\Event\CallEventFactory;
use Eloquent\Phony\Clock\SystemClock;
use Eloquent\Phony\Event\EventOrderVerifier;
use Eloquent\Phony\Event\NullEvent;
use Eloquent\Phony\Exporter\InlineExporter;
use Eloquent\Phony\Facade\FacadeDriver;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Matcher\AnyMatcher;
use Eloquent\Phony\Matcher\MatcherFactory;
use Eloquent\Phony\Matcher\MatcherVerifier;
use Eloquent\Phony\Matcher\WildcardMatcher;
use Eloquent\Phony\Mock\Builder\MockBuilderFactory;
use Eloquent\Phony\Mock\Handle\HandleFactory;
use Eloquent\Phony\Mock\MockFactory;
use Eloquent\Phony\Mock\MockGenerator;
use Eloquent\Phony\Phpunit\PhpunitMatcherDriver;
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Reflection\FunctionSignatureInspector;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Simpletest\SimpletestMatcherDriver;
use Eloquent\Phony\Spy\GeneratorSpyFactory;
use Eloquent\Phony\Spy\SpyFactory;
use Eloquent\Phony\Spy\SpyVerifierFactory;
use Eloquent\Phony\Spy\TraversableSpyFactory;
use Eloquent\Phony\Stub\Answer\Builder\GeneratorAnswerBuilderFactory;
use Eloquent\Phony\Stub\StubFactory;
use Eloquent\Phony\Stub\StubVerifierFactory;

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

        $anyMatcher = new AnyMatcher();
        $exporter = new InlineExporter(1, true);
        $featureDetector = new FeatureDetector();
        $invocableInspector = new InvocableInspector();
        $invoker = new Invoker();
        $matcherVerifier = new MatcherVerifier();
        $nullEvent = new NullEvent();

        $functionSignatureInspector = new FunctionSignatureInspector(
            $invocableInspector,
            $featureDetector
        );
        $mockGenerator = new MockGenerator(
            Sequencer::sequence('mock-class-label'),
            $functionSignatureInspector,
            $featureDetector
        );
        $wildcardMatcher = new WildcardMatcher(
            $anyMatcher,
            0,
            null
        );
        $matcherFactory = new MatcherFactory(
            $anyMatcher,
            $wildcardMatcher,
            $exporter
        );
        $matcherFactory->addMatcherDriver(new HamcrestMatcherDriver());
        $matcherFactory->addMatcherDriver(new CounterpartMatcherDriver());
        $matcherFactory->addMatcherDriver(new PhpunitMatcherDriver());
        $matcherFactory->addMatcherDriver(new SimpletestMatcherDriver());
        $matcherFactory
            ->addMatcherDriver(new PhakeMatcherDriver($wildcardMatcher));
        $matcherFactory
            ->addMatcherDriver(new ProphecyMatcherDriver($wildcardMatcher));
        $matcherFactory->addMatcherDriver(new MockeryMatcherDriver());
        $generatorAnswerBuilderFactory = new GeneratorAnswerBuilderFactory(
            $invocableInspector,
            $invoker,
            $featureDetector
        );
        $stubFactory = new StubFactory(
            Sequencer::sequence('stub-label'),
            $matcherFactory,
            $matcherVerifier,
            $invoker,
            $invocableInspector,
            $generatorAnswerBuilderFactory
        );
        $clock = new SystemClock('microtime');
        $eventFactory = new CallEventFactory(
            Sequencer::sequence('event-sequence-number'),
            $clock
        );
        $callFactory = new CallFactory(
            $eventFactory,
            $invoker
        );
        $generatorSpyFactory = new GeneratorSpyFactory(
            $eventFactory,
            $featureDetector
        );
        $traversableSpyFactory = new TraversableSpyFactory(
            $eventFactory
        );
        $spyFactory = new SpyFactory(
            Sequencer::sequence('spy-label'),
            $callFactory,
            $invoker,
            $generatorSpyFactory,
            $traversableSpyFactory
        );
        $assertionRenderer = new AssertionRenderer(
            $invocableInspector,
            $exporter
        );
        $callVerifierFactory = new CallVerifierFactory(
            $matcherFactory,
            $matcherVerifier,
            $assertionRecorder,
            $assertionRenderer,
            $invocableInspector
        );
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
            $generatorAnswerBuilderFactory
        );
        $handleFactory = new HandleFactory(
            $stubFactory,
            $stubVerifierFactory,
            $assertionRenderer,
            $assertionRecorder,
            $invoker
        );
        $mockFactory = new MockFactory(
            Sequencer::sequence('mock-label'),
            $mockGenerator,
            $handleFactory
        );
        $mockBuilderFactory = new MockBuilderFactory(
            $mockFactory,
            $handleFactory,
            $invocableInspector,
            $featureDetector
        );
        $spyVerifierFactory = new SpyVerifierFactory(
            $spyFactory,
            $matcherFactory,
            $matcherVerifier,
            $callVerifierFactory,
            $assertionRecorder,
            $assertionRenderer,
            $invocableInspector
        );
        $eventOrderVerifier = new EventOrderVerifier(
            $assertionRecorder,
            $assertionRenderer,
            $nullEvent
        );

        parent::__construct(
            $mockBuilderFactory,
            $handleFactory,
            $spyVerifierFactory,
            $stubVerifierFactory,
            $eventOrderVerifier,
            $matcherFactory,
            $exporter
        );
    }

    /**
     * Create the assertion recorder.
     *
     * @return AssertionRecorder The assertion recorder.
     */
    abstract protected function createAssertionRecorder();
}
