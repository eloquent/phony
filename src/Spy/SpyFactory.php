<?php

declare(strict_types=1);

namespace Eloquent\Phony\Spy;

use Eloquent\Phony\Call\CallFactory;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Invocation\WrappedInvocable;
use Eloquent\Phony\Sequencer\Sequencer;

/**
 * Creates spies.
 */
class SpyFactory
{
    /**
     * Construct a new spy factory.
     *
     * @param Sequencer           $labelSequencer      The label sequencer to use.
     * @param CallFactory         $callFactory         The call factory to use.
     * @param Invoker             $invoker             The invoker to use.
     * @param GeneratorSpyFactory $generatorSpyFactory The generator spy factory to use.
     * @param IterableSpyFactory  $iterableSpyFactory  The iterable spy factory to use.
     * @param InvocableInspector  $invocableInspector  The invocable inspector to use.
     */
    public function __construct(
        Sequencer $labelSequencer,
        CallFactory $callFactory,
        Invoker $invoker,
        GeneratorSpyFactory $generatorSpyFactory,
        IterableSpyFactory $iterableSpyFactory,
        InvocableInspector $invocableInspector
    ) {
        $this->labelSequencer = $labelSequencer;
        $this->callFactory = $callFactory;
        $this->invoker = $invoker;
        $this->generatorSpyFactory = $generatorSpyFactory;
        $this->iterableSpyFactory = $iterableSpyFactory;
        $this->invocableInspector = $invocableInspector;
    }

    /**
     * Create a new spy.
     *
     * @param ?callable $callback The callback, or null to create an anonymous spy.
     *
     * @return Spy The newly created spy.
     */
    public function create(?callable $callback): Spy
    {
        if ($callback) {
            if ($callback instanceof WrappedInvocable) {
                $parameters = $callback->parameters();
            } else {
                $parameters = $this->invocableInspector
                    ->callbackReflector($callback)->getParameters();
            }
        } else {
            $parameters = [];
        }

        return new SpyData(
            $callback,
            $parameters,
            strval($this->labelSequencer->next()),
            $this->callFactory,
            $this->invoker,
            $this->generatorSpyFactory,
            $this->iterableSpyFactory
        );
    }

    /**
     * @var Sequencer
     */
    private $labelSequencer;

    /**
     * @var CallFactory
     */
    private $callFactory;

    /**
     * @var Invoker
     */
    private $invoker;

    /**
     * @var GeneratorSpyFactory
     */
    private $generatorSpyFactory;

    /**
     * @var IterableSpyFactory
     */
    private $iterableSpyFactory;

    /**
     * @var InvocableInspector
     */
    private $invocableInspector;
}
