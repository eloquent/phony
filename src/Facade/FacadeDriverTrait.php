<?php

declare(strict_types=1);

namespace Eloquent\Phony\Facade;

use Eloquent\Phony\Assertion\AssertionRecorder;
use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Call\CallFactory;
use Eloquent\Phony\Call\CallVerifierFactory;
use Eloquent\Phony\Call\Event\CallEventFactory;
use Eloquent\Phony\Clock\SystemClock;
use Eloquent\Phony\Difference\DifferenceEngine;
use Eloquent\Phony\Event\EventOrderVerifier;
use Eloquent\Phony\Exporter\InlineExporter;
use Eloquent\Phony\Hamcrest\HamcrestMatcherDriver;
use Eloquent\Phony\Hook\FunctionHookGenerator;
use Eloquent\Phony\Hook\FunctionHookManager;
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
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Reflection\HhvmFunctionSignatureInspector;
use Eloquent\Phony\Reflection\PhpFunctionSignatureInspector;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Spy\GeneratorSpyFactory;
use Eloquent\Phony\Spy\IterableSpyFactory;
use Eloquent\Phony\Spy\SpyFactory;
use Eloquent\Phony\Spy\SpyVerifierFactory;
use Eloquent\Phony\Stub\Answer\Builder\GeneratorAnswerBuilderFactory;
use Eloquent\Phony\Stub\EmptyValueFactory;
use Eloquent\Phony\Stub\StubFactory;
use Eloquent\Phony\Stub\StubVerifierFactory;
use Eloquent\Phony\Verification\GeneratorVerifierFactory;
use Eloquent\Phony\Verification\IterableVerifierFactory;

/**
 * Used for implementing facade drivers.
 */
trait FacadeDriverTrait
{
    private function initializeFacadeDriver(
        AssertionRecorder $assertionRecorder
    ) {
        $this->sequences = [];

        $anyMatcher = new AnyMatcher();
        $objectIdSequence = Sequencer::sequence('exporter-object-id');
        $invocableInspector = new InvocableInspector();
        $exporter = new InlineExporter(
            1,
            $objectIdSequence,
            $invocableInspector
        );
        $featureDetector = new FeatureDetector();
        $invoker = new Invoker();
        $matcherVerifier = new MatcherVerifier();

        if ($featureDetector->isSupported('runtime.hhvm')) {
            // @codeCoverageIgnoreStart
            $functionSignatureInspector =
                new HhvmFunctionSignatureInspector($featureDetector);
            // @codeCoverageIgnoreEnd
        } else {
            $functionSignatureInspector =
                new PhpFunctionSignatureInspector($featureDetector);
        }

        $mockClassLabelSequence = Sequencer::sequence('mock-class-label');
        $this->sequences[] = $mockClassLabelSequence;
        $mockGenerator = new MockGenerator(
            $mockClassLabelSequence,
            $functionSignatureInspector,
            $featureDetector
        );
        $wildcardMatcher = new WildcardMatcher(
            $anyMatcher,
            0,
            -1
        );
        $matcherFactory = new MatcherFactory(
            $anyMatcher,
            $wildcardMatcher,
            $exporter
        );
        $matcherFactory->addMatcherDriver(new HamcrestMatcherDriver());
        $emptyValueFactory = new EmptyValueFactory(
            $featureDetector
        );
        $generatorAnswerBuilderFactory = new GeneratorAnswerBuilderFactory(
            $invocableInspector,
            $invoker
        );
        $stubLabelSequence = Sequencer::sequence('stub-label');
        $this->sequences[] = $stubLabelSequence;
        $stubFactory = new StubFactory(
            $stubLabelSequence,
            $matcherFactory,
            $matcherVerifier,
            $invoker,
            $invocableInspector,
            $emptyValueFactory,
            $generatorAnswerBuilderFactory
        );
        $clock = new SystemClock('microtime');
        $eventSequence = Sequencer::sequence('event-sequence-number');
        $this->sequences[] = $eventSequence;
        $eventFactory = new CallEventFactory(
            $eventSequence,
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
        $iterableSpyFactory = new IterableSpyFactory(
            $eventFactory
        );
        $spyLabelSequence = Sequencer::sequence('spy-label');
        $this->sequences[] = $spyLabelSequence;
        $spyFactory = new SpyFactory(
            $spyLabelSequence,
            $callFactory,
            $invoker,
            $generatorSpyFactory,
            $iterableSpyFactory
        );
        $differenceEngine = new DifferenceEngine(
            $featureDetector
        );
        $assertionRenderer = new AssertionRenderer(
            $matcherVerifier,
            $exporter,
            $differenceEngine,
            $featureDetector
        );
        $generatorVerifierFactory = new GeneratorVerifierFactory(
            $matcherFactory,
            $assertionRecorder,
            $assertionRenderer
        );
        $iterableVerifierFactory = new IterableVerifierFactory(
            $matcherFactory,
            $assertionRecorder,
            $assertionRenderer
        );
        $callVerifierFactory = new CallVerifierFactory(
            $matcherFactory,
            $matcherVerifier,
            $generatorVerifierFactory,
            $iterableVerifierFactory,
            $assertionRecorder,
            $assertionRenderer
        );
        $assertionRecorder->setCallVerifierFactory($callVerifierFactory);
        $functionHookGenerator = new FunctionHookGenerator();
        $functionHookManager = new FunctionHookManager(
            $invocableInspector,
            $functionSignatureInspector,
            $functionHookGenerator
        );
        $stubVerifierFactory = new StubVerifierFactory(
            $stubFactory,
            $spyFactory,
            $matcherFactory,
            $matcherVerifier,
            $generatorVerifierFactory,
            $iterableVerifierFactory,
            $callVerifierFactory,
            $assertionRecorder,
            $assertionRenderer,
            $generatorAnswerBuilderFactory,
            $functionHookManager
        );
        $handleFactory = new HandleFactory(
            $stubFactory,
            $stubVerifierFactory,
            $emptyValueFactory,
            $assertionRenderer,
            $assertionRecorder,
            $invoker
        );
        $mockLabelSequence = Sequencer::sequence('mock-label');
        $this->sequences[] = $mockLabelSequence;
        $mockFactory = new MockFactory(
            $mockLabelSequence,
            $mockGenerator,
            $handleFactory
        );
        $mockBuilderFactory = new MockBuilderFactory(
            $mockFactory,
            $handleFactory,
            $invocableInspector
        );
        $spyVerifierFactory = new SpyVerifierFactory(
            $spyFactory,
            $matcherFactory,
            $matcherVerifier,
            $generatorVerifierFactory,
            $iterableVerifierFactory,
            $callVerifierFactory,
            $assertionRecorder,
            $assertionRenderer,
            $functionHookManager
        );
        $eventOrderVerifier = new EventOrderVerifier(
            $assertionRecorder,
            $assertionRenderer
        );

        $emptyValueFactory->setStubVerifierFactory($stubVerifierFactory);
        $emptyValueFactory->setMockBuilderFactory($mockBuilderFactory);
        $generatorVerifierFactory->setCallVerifierFactory($callVerifierFactory);
        $iterableVerifierFactory
            ->setCallVerifierFactory($callVerifierFactory);

        $this->mockBuilderFactory = $mockBuilderFactory;
        $this->handleFactory = $handleFactory;
        $this->spyVerifierFactory = $spyVerifierFactory;
        $this->stubVerifierFactory = $stubVerifierFactory;
        $this->functionHookManager = $functionHookManager;
        $this->eventOrderVerifier = $eventOrderVerifier;
        $this->matcherFactory = $matcherFactory;
        $this->exporter = $exporter;
        $this->assertionRenderer = $assertionRenderer;
        $this->differenceEngine = $differenceEngine;
    }

    public $mockBuilderFactory;
    public $handleFactory;
    public $spyVerifierFactory;
    public $stubVerifierFactory;
    public $functionHookManager;
    public $eventOrderVerifier;
    public $matcherFactory;
    public $exporter;
    public $assertionRenderer;
    public $differenceEngine;
    protected $sequences;
}
