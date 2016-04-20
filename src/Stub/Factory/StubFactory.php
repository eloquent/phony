<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub\Factory;

use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\InvocableInspectorInterface;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Invocation\InvokerInterface;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Factory\MatcherFactoryInterface;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Matcher\Verification\MatcherVerifierInterface;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Sequencer\SequencerInterface;
use Eloquent\Phony\Stub\Answer\Builder\Factory\GeneratorAnswerBuilderFactory;
use Eloquent\Phony\Stub\Answer\Builder\Factory\GeneratorAnswerBuilderFactoryInterface;
use Eloquent\Phony\Stub\Stub;

/**
 * Creates stubs.
 */
class StubFactory implements StubFactoryInterface
{
    /**
     * Get the static instance of this factory.
     *
     * @return StubFactoryInterface The static factory.
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self(
                Sequencer::sequence('stub-label'),
                MatcherFactory::instance(),
                MatcherVerifier::instance(),
                Invoker::instance(),
                InvocableInspector::instance(),
                GeneratorAnswerBuilderFactory::instance()
            );
        }

        return self::$instance;
    }

    /**
     * Construct a new stub factory.
     *
     * @param SequencerInterface                     $labelSequencer                The label sequencer to use.
     * @param MatcherFactoryInterface                $matcherFactory                The matcher factory to use.
     * @param MatcherVerifierInterface               $matcherVerifier               The matcher verifier to use.
     * @param InvokerInterface                       $invoker                       The invoker to use.
     * @param InvocableInspectorInterface            $invocableInspector            The invocable inspector to use.
     * @param GeneratorAnswerBuilderFactoryInterface $generatorAnswerBuilderFactory The generator answer builder factory to use.
     */
    public function __construct(
        SequencerInterface $labelSequencer,
        MatcherFactoryInterface $matcherFactory,
        MatcherVerifierInterface $matcherVerifier,
        InvokerInterface $invoker,
        InvocableInspectorInterface $invocableInspector,
        GeneratorAnswerBuilderFactoryInterface $generatorAnswerBuilderFactory
    ) {
        $this->labelSequencer = $labelSequencer;
        $this->matcherFactory = $matcherFactory;
        $this->matcherVerifier = $matcherVerifier;
        $this->invoker = $invoker;
        $this->invocableInspector = $invocableInspector;
        $this->generatorAnswerBuilderFactory = $generatorAnswerBuilderFactory;
    }

    /**
     * Create a new stub.
     *
     * @param callable|null $callback              The callback, or null to create an anonymous stub.
     * @param mixed         $self                  The self value.
     * @param callable      $defaultAnswerCallback The callback to use when creating a default answer.
     *
     * @return StubInterface The newly created stub.
     */
    public function create(
        $callback = null,
        $self = null,
        $defaultAnswerCallback =
            'Eloquent\Phony\Stub\Stub::forwardsAnswerCallback'
    ) {
        return new Stub(
            $callback,
            $self,
            strval($this->labelSequencer->next()),
            $defaultAnswerCallback,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->invoker,
            $this->invocableInspector,
            $this->generatorAnswerBuilderFactory
        );
    }

    private static $instance;
    private $labelSequencer;
    private $matcherFactory;
    private $matcherVerifier;
    private $invoker;
    private $invocableInspector;
    private $generatorAnswerBuilderFactory;
}
