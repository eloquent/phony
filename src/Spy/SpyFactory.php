<?php

declare(strict_types=1);

namespace Eloquent\Phony\Spy;

use Eloquent\Phony\Call\CallFactory;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Sequencer\Sequencer;

/**
 * Creates spies.
 */
class SpyFactory
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
                Sequencer::sequence('spy-label'),
                CallFactory::instance(),
                Invoker::instance(),
                GeneratorSpyFactory::instance(),
                IterableSpyFactory::instance()
            );
        }

        return self::$instance;
    }

    /**
     * Construct a new spy factory.
     *
     * @param Sequencer           $labelSequencer      The label sequencer to use.
     * @param CallFactory         $callFactory         The call factory to use.
     * @param Invoker             $invoker             The invoker to use.
     * @param GeneratorSpyFactory $generatorSpyFactory The generator spy factory to use.
     * @param IterableSpyFactory  $iterableSpyFactory  The iterable spy factory to use.
     */
    public function __construct(
        Sequencer $labelSequencer,
        CallFactory $callFactory,
        Invoker $invoker,
        GeneratorSpyFactory $generatorSpyFactory,
        IterableSpyFactory $iterableSpyFactory
    ) {
        $this->labelSequencer = $labelSequencer;
        $this->callFactory = $callFactory;
        $this->invoker = $invoker;
        $this->generatorSpyFactory = $generatorSpyFactory;
        $this->iterableSpyFactory = $iterableSpyFactory;
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
        return new SpyData(
            $callback,
            strval($this->labelSequencer->next()),
            $this->callFactory,
            $this->invoker,
            $this->generatorSpyFactory,
            $this->iterableSpyFactory
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
}
