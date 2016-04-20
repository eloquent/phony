<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy\Factory;

use Eloquent\Phony\Call\Factory\CallFactory;
use Eloquent\Phony\Call\Factory\CallFactoryInterface;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Invocation\InvokerInterface;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Sequencer\SequencerInterface;
use Eloquent\Phony\Spy\Spy;
use Eloquent\Phony\Spy\SpyInterface;

/**
 * Creates spies.
 */
class SpyFactory implements SpyFactoryInterface
{
    /**
     * Get the static instance of this factory.
     *
     * @return SpyFactoryInterface The static factory.
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self(
                Sequencer::sequence('spy-label'),
                CallFactory::instance(),
                Invoker::instance(),
                GeneratorSpyFactory::instance(),
                TraversableSpyFactory::instance()
            );
        }

        return self::$instance;
    }

    /**
     * Construct a new spy factory.
     *
     * @param SequencerInterface             $labelSequencer        The label sequencer to use.
     * @param CallFactoryInterface           $callFactory           The call factory to use.
     * @param InvokerInterface               $invoker               The invoker to use.
     * @param TraversableSpyFactoryInterface $generatorSpyFactory   The generator spy factory to use.
     * @param TraversableSpyFactoryInterface $traversableSpyFactory The traversable spy factory to use.
     */
    public function __construct(
        SequencerInterface $labelSequencer,
        CallFactoryInterface $callFactory,
        InvokerInterface $invoker,
        TraversableSpyFactoryInterface $generatorSpyFactory,
        TraversableSpyFactoryInterface $traversableSpyFactory
    ) {
        $this->labelSequencer = $labelSequencer;
        $this->callFactory = $callFactory;
        $this->invoker = $invoker;
        $this->generatorSpyFactory = $generatorSpyFactory;
        $this->traversableSpyFactory = $traversableSpyFactory;
    }

    /**
     * Create a new spy.
     *
     * @param callable|null $callback The callback, or null to create an anonymous spy.
     *
     * @return SpyInterface The newly created spy.
     */
    public function create($callback = null)
    {
        return new Spy(
            $callback,
            strval($this->labelSequencer->next()),
            $this->callFactory,
            $this->invoker,
            $this->generatorSpyFactory,
            $this->traversableSpyFactory
        );
    }

    private static $instance;
    private $labelSequencer;
    private $callFactory;
    private $invoker;
    private $generatorSpyFactory;
    private $traversableSpyFactory;
}
