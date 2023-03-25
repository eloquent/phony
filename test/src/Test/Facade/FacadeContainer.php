<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test\Facade;

use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Assertion\ExceptionAssertionRecorder;
use Eloquent\Phony\Call\ArgumentNormalizer;
use Eloquent\Phony\Call\CallFactory;
use Eloquent\Phony\Call\CallVerifierFactory;
use Eloquent\Phony\Call\Event\CallEventFactory;
use Eloquent\Phony\Clock\Clock;
use Eloquent\Phony\Clock\SystemClock;
use Eloquent\Phony\Difference\DifferenceEngine;
use Eloquent\Phony\Event\EventOrderVerifier;
use Eloquent\Phony\Exporter\Exporter;
use Eloquent\Phony\Exporter\InlineExporter;
use Eloquent\Phony\Hamcrest\HamcrestMatcherDriver;
use Eloquent\Phony\Hook\FunctionHookGenerator;
use Eloquent\Phony\Hook\FunctionHookManager;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Matcher\AnyMatcher;
use Eloquent\Phony\Matcher\MatcherDriver;
use Eloquent\Phony\Matcher\MatcherFactory;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Matcher\WildcardMatcher;
use Eloquent\Phony\Mock\Builder\MockBuilderFactory;
use Eloquent\Phony\Mock\Handle\HandleFactory;
use Eloquent\Phony\Mock\MockFactory;
use Eloquent\Phony\Mock\MockGenerator;
use Eloquent\Phony\Mock\MockRegistry;
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Reflection\FunctionSignatureInspector;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Spy\GeneratorSpyFactory;
use Eloquent\Phony\Spy\GeneratorSpyMap;
use Eloquent\Phony\Spy\IterableSpyFactory;
use Eloquent\Phony\Spy\SpyFactory;
use Eloquent\Phony\Spy\SpyVerifierFactory;
use Eloquent\Phony\Stub\Answer\Builder\GeneratorAnswerBuilderFactory;
use Eloquent\Phony\Stub\EmptyValueFactory;
use Eloquent\Phony\Stub\StubFactory;
use Eloquent\Phony\Stub\StubVerifierFactory;
use Eloquent\Phony\Test\TestCallFactory;
use Eloquent\Phony\Verification\GeneratorVerifierFactory;
use Eloquent\Phony\Verification\IterableVerifierFactory;

class FacadeContainer
{
    public static function withTestCallFactory(): self
    {
        $callFactory = new TestCallFactory();
        $eventFactory = $callFactory->eventFactory();

        return new self(
            callFactory: $callFactory,
            eventFactory: $eventFactory,
        );
    }

    public function __construct(
        public ?AnyMatcher $anyMatcher = null,
        public ?ArgumentNormalizer $argumentNormalizer = null,
        public ?array $sequences = null,
        public ?AssertionRenderer $assertionRenderer = null,
        public ?CallEventFactory $eventFactory = null,
        public ?CallFactory $callFactory = null,
        public ?CallVerifierFactory $callVerifierFactory = null,
        public ?Clock $clock = null,
        public ?DifferenceEngine $differenceEngine = null,
        public ?EmptyValueFactory $emptyValueFactory = null,
        public ?EventOrderVerifier $eventOrderVerifier = null,
        public ?ExceptionAssertionRecorder $assertionRecorder = null,
        public ?Exporter $exporter = null,
        public ?FeatureDetector $featureDetector = null,
        public ?FunctionHookGenerator $functionHookGenerator = null,
        public ?FunctionHookManager $functionHookManager = null,
        public ?FunctionSignatureInspector $functionSignatureInspector = null,
        public ?GeneratorAnswerBuilderFactory $generatorAnswerBuilderFactory = null,
        public ?GeneratorSpyFactory $generatorSpyFactory = null,
        public ?GeneratorSpyMap $generatorSpyMap = null,
        public ?GeneratorVerifierFactory $generatorVerifierFactory = null,
        public ?HandleFactory $handleFactory = null,
        public ?InvocableInspector $invocableInspector = null,
        public ?Invoker $invoker = null,
        public ?IterableSpyFactory $iterableSpyFactory = null,
        public ?IterableVerifierFactory $iterableVerifierFactory = null,
        public ?MatcherDriver $hamcrestMatcherDriver = null,
        public ?MatcherFactory $matcherFactory = null,
        public ?MatcherVerifier $matcherVerifier = null,
        public ?MockBuilderFactory $mockBuilderFactory = null,
        public ?MockFactory $mockFactory = null,
        public ?MockGenerator $mockGenerator = null,
        public ?MockRegistry $mockRegistry = null,
        public ?Sequencer $eventSequence = null,
        public ?Sequencer $idSequence = null,
        public ?Sequencer $mockClassLabelSequence = null,
        public ?Sequencer $mockLabelSequence = null,
        public ?Sequencer $spyLabelSequence = null,
        public ?Sequencer $stubLabelSequence = null,
        public ?SpyFactory $spyFactory = null,
        public ?SpyVerifierFactory $spyVerifierFactory = null,
        public ?StubFactory $stubFactory = null,
        public ?StubVerifierFactory $stubVerifierFactory = null,
        public ?WildcardMatcher $wildcardMatcher = null,
    ) {
        $this->assertionRecorder ??= new ExceptionAssertionRecorder();

        $this->sequences ??= [];
        $this->anyMatcher ??= new AnyMatcher();
        $this->idSequence ??= new Sequencer();
        $this->invocableInspector ??= new InvocableInspector();
        $this->generatorSpyMap ??= new GeneratorSpyMap();
        $this->exporter ??= new InlineExporter(
            1,
            $this->idSequence,
            $this->generatorSpyMap,
            $this->invocableInspector
        );
        $this->invoker ??= new Invoker();
        $this->matcherVerifier ??= new MatcherVerifier();
        $this->functionSignatureInspector ??= new FunctionSignatureInspector();
        $this->mockClassLabelSequence ??= Sequencer::sequence('mock-class-label');
        $this->sequences[] = $this->mockClassLabelSequence;
        $this->featureDetector ??= new FeatureDetector();
        $this->mockGenerator ??= new MockGenerator(
            $this->mockClassLabelSequence,
            $this->functionSignatureInspector,
            $this->featureDetector
        );
        $this->wildcardMatcher ??= new WildcardMatcher(
            $this->anyMatcher,
            0,
            -1
        );
        $this->matcherFactory ??= new MatcherFactory(
            $this->anyMatcher,
            $this->wildcardMatcher,
            $this->exporter
        );
        $this->hamcrestMatcherDriver ??= new HamcrestMatcherDriver();
        $this->matcherFactory->addMatcherDriver($this->hamcrestMatcherDriver);
        $this->emptyValueFactory ??= new EmptyValueFactory($this->featureDetector);
        $this->generatorAnswerBuilderFactory ??= new GeneratorAnswerBuilderFactory(
            $this->invocableInspector,
            $this->invoker
        );
        $this->stubLabelSequence ??= new Sequencer();
        $this->sequences[] = $this->stubLabelSequence;
        $this->differenceEngine ??= new DifferenceEngine(
            $this->featureDetector
        );
        $this->argumentNormalizer ??= new ArgumentNormalizer();
        $this->assertionRenderer ??= new AssertionRenderer(
            $this->matcherVerifier,
            $this->exporter,
            $this->differenceEngine,
            $this->featureDetector,
            $this->argumentNormalizer
        );
        $this->stubFactory ??= new StubFactory(
            $this->stubLabelSequence,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->invoker,
            $this->invocableInspector,
            $this->emptyValueFactory,
            $this->generatorAnswerBuilderFactory,
            $this->exporter,
            $this->assertionRenderer
        );
        $this->clock ??= new SystemClock('microtime');
        $this->eventSequence ??= new Sequencer();
        $this->sequences[] = $this->eventSequence;
        $this->eventFactory ??= new CallEventFactory(
            $this->eventSequence,
            $this->clock
        );
        $this->callFactory ??= new CallFactory(
            $this->eventFactory,
            $this->invoker
        );
        $this->generatorSpyFactory ??= new GeneratorSpyFactory(
            $this->eventFactory,
            $this->generatorSpyMap
        );
        $this->iterableSpyFactory ??= new IterableSpyFactory(
            $this->eventFactory
        );
        $this->spyLabelSequence ??= new Sequencer();
        $this->sequences[] = $this->spyLabelSequence;
        $this->spyFactory ??= new SpyFactory(
            $this->spyLabelSequence,
            $this->callFactory,
            $this->invoker,
            $this->generatorSpyFactory,
            $this->iterableSpyFactory,
            $this->invocableInspector
        );
        $this->generatorVerifierFactory ??= new GeneratorVerifierFactory(
            $this->matcherFactory,
            $this->assertionRecorder,
            $this->assertionRenderer
        );
        $this->iterableVerifierFactory ??= new IterableVerifierFactory(
            $this->matcherFactory,
            $this->assertionRecorder,
            $this->assertionRenderer
        );
        $this->callVerifierFactory ??= new CallVerifierFactory(
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->iterableVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer
        );
        $this->assertionRecorder->setCallVerifierFactory($this->callVerifierFactory);
        $this->functionHookGenerator ??= new FunctionHookGenerator();
        $this->functionHookManager ??= new FunctionHookManager(
            $this->invocableInspector,
            $this->functionSignatureInspector,
            $this->functionHookGenerator
        );
        $this->stubVerifierFactory ??= new StubVerifierFactory(
            $this->stubFactory,
            $this->spyFactory,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->iterableVerifierFactory,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->generatorAnswerBuilderFactory,
            $this->functionHookManager
        );
        $this->mockRegistry ??= new MockRegistry();
        $this->handleFactory ??= new HandleFactory(
            $this->mockRegistry,
            $this->stubFactory,
            $this->stubVerifierFactory,
            $this->emptyValueFactory,
            $this->assertionRenderer,
            $this->assertionRecorder,
            $this->invoker
        );
        $this->mockLabelSequence ??= new Sequencer();
        $this->sequences[] = $this->mockLabelSequence;
        $this->mockFactory ??= new MockFactory(
            $this->mockLabelSequence,
            $this->mockGenerator,
            $this->mockRegistry,
            $this->handleFactory
        );
        $this->mockBuilderFactory ??= new MockBuilderFactory(
            $this->mockGenerator,
            $this->mockFactory,
            $this->handleFactory,
            $this->invocableInspector,
            $this->featureDetector
        );
        $this->spyVerifierFactory ??= new SpyVerifierFactory(
            $this->spyFactory,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->iterableVerifierFactory,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->functionHookManager
        );
        $this->eventOrderVerifier ??= new EventOrderVerifier(
            $this->assertionRecorder,
            $this->assertionRenderer
        );

        $this->emptyValueFactory->setStubVerifierFactory($this->stubVerifierFactory);
        $this->emptyValueFactory->setMockBuilderFactory($this->mockBuilderFactory);
        $this->generatorVerifierFactory->setCallVerifierFactory($this->callVerifierFactory);
        $this->iterableVerifierFactory
            ->setCallVerifierFactory($this->callVerifierFactory);
    }
}
