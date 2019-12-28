<?php

declare(strict_types=1);

namespace Eloquent\Phony\Stub;

use Eloquent\Phony\Exporter\Exporter;
use Eloquent\Phony\Exporter\InlineExporter;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Matcher\MatcherFactory;
use Eloquent\Phony\Matcher\MatcherVerifier;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Stub\Answer\Builder\GeneratorAnswerBuilderFactory;

/**
 * Creates stubs.
 */
class StubFactory
{
    /**
     * Get the static instance of this class.
     *
     * @return self The static instance.
     */
    public static function instance(): self
    {
        if (!self::$instance) {
            self::$instance = new self(
                Sequencer::sequence('stub-label'),
                MatcherFactory::instance(),
                MatcherVerifier::instance(),
                Invoker::instance(),
                InvocableInspector::instance(),
                EmptyValueFactory::instance(),
                GeneratorAnswerBuilderFactory::instance(),
                InlineExporter::instance()
            );
        }

        return self::$instance;
    }

    /**
     * Construct a new stub factory.
     *
     * @param Sequencer                     $labelSequencer                The label sequencer to use.
     * @param MatcherFactory                $matcherFactory                The matcher factory to use.
     * @param MatcherVerifier               $matcherVerifier               The matcher verifier to use.
     * @param Invoker                       $invoker                       The invoker to use.
     * @param InvocableInspector            $invocableInspector            The invocable inspector to use.
     * @param EmptyValueFactory             $emptyValueFactory             The empty value factory to use.
     * @param GeneratorAnswerBuilderFactory $generatorAnswerBuilderFactory The generator answer builder factory to use.
     * @param Exporter                      $exporter                      The exporter to use.
     */
    public function __construct(
        Sequencer $labelSequencer,
        MatcherFactory $matcherFactory,
        MatcherVerifier $matcherVerifier,
        Invoker $invoker,
        InvocableInspector $invocableInspector,
        EmptyValueFactory $emptyValueFactory,
        GeneratorAnswerBuilderFactory $generatorAnswerBuilderFactory,
        Exporter $exporter
    ) {
        $this->labelSequencer = $labelSequencer;
        $this->matcherFactory = $matcherFactory;
        $this->matcherVerifier = $matcherVerifier;
        $this->invoker = $invoker;
        $this->invocableInspector = $invocableInspector;
        $this->emptyValueFactory = $emptyValueFactory;
        $this->generatorAnswerBuilderFactory = $generatorAnswerBuilderFactory;
        $this->exporter = $exporter;
    }

    /**
     * Create a new stub.
     *
     * @param ?callable $callback              The callback, or null to create an anonymous stub.
     * @param ?callable $defaultAnswerCallback The callback to use when creating a default answer.
     *
     * @return Stub The newly created stub.
     */
    public function create(
        ?callable $callback,
        ?callable $defaultAnswerCallback
    ): Stub {
        if (null === $defaultAnswerCallback) {
            $defaultAnswerCallback =
                [StubData::class, 'returnsEmptyAnswerCallback'];
        }

        return new StubData(
            $callback,
            strval($this->labelSequencer->next()),
            $defaultAnswerCallback,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->invoker,
            $this->invocableInspector,
            $this->emptyValueFactory,
            $this->generatorAnswerBuilderFactory,
            $this->exporter
        );
    }

    /**
     * @var ?self
     */
    private static $instance;

    /**
     * @var Sequencer
     */
    private $labelSequencer;

    /**
     * @var MatcherFactory
     */
    private $matcherFactory;

    /**
     * @var MatcherVerifier
     */
    private $matcherVerifier;

    /**
     * @var Invoker
     */
    private $invoker;

    /**
     * @var InvocableInspector
     */
    private $invocableInspector;

    /**
     * @var EmptyValueFactory
     */
    private $emptyValueFactory;

    /**
     * @var GeneratorAnswerBuilderFactory
     */
    private $generatorAnswerBuilderFactory;

    /**
     * @var Exporter
     */
    private $exporter;
}
