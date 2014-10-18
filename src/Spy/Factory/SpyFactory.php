<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy\Factory;

use Eloquent\Phony\Call\Factory\CallFactory;
use Eloquent\Phony\Call\Factory\CallFactoryInterface;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Sequencer\SequencerInterface;
use Eloquent\Phony\Spy\Spy;
use Eloquent\Phony\Spy\SpyInterface;

/**
 * Creates spies.
 *
 * @internal
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
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Construct a new spy factory.
     *
     * @param SequencerInterface|null             $idSequencer           The identifier sequencer to use.
     * @param CallFactoryInterface|null           $callFactory           The call factory to use.
     * @param TraversableSpyFactoryInterface|null $traversableSpyFactory The traversable spy factory to use.
     */
    public function __construct(
        SequencerInterface $idSequencer = null,
        CallFactoryInterface $callFactory = null,
        TraversableSpyFactoryInterface $traversableSpyFactory = null
    ) {
        if (null === $idSequencer) {
            $idSequencer = Sequencer::sequence('spy-id');
        }
        if (null === $callFactory) {
            $callFactory = CallFactory::instance();
        }
        if (null === $traversableSpyFactory) {
            $traversableSpyFactory = TraversableSpyFactory::instance();
        }

        $this->idSequencer = $idSequencer;
        $this->callFactory = $callFactory;
        $this->traversableSpyFactory = $traversableSpyFactory;
    }

    /**
     * Get the identifier sequencer.
     *
     * @return SequencerInterface The identifier sequencer.
     */
    public function idSequencer()
    {
        return $this->idSequencer;
    }

    /**
     * Get the call factory.
     *
     * @return CallFactoryInterface The call factory.
     */
    public function callFactory()
    {
        return $this->callFactory;
    }

    /**
     * Get the traversable spy factory.
     *
     * @return TraversableSpyFactoryInterface The traversable spy factory.
     */
    public function traversableSpyFactory()
    {
        return $this->traversableSpyFactory;
    }

    /**
     * Create a new spy.
     *
     * @param callable|null $callback            The callback, or null to create an unbound spy.
     * @param boolean|null  $useTraversableSpies True if traversable spies should be used.
     * @param boolean|null  $useGeneratorSpies   True if generator spies should be used.
     *
     * @return SpyInterface The newly created spy.
     */
    public function create(
        $callback = null,
        $useTraversableSpies = null,
        $useGeneratorSpies = null
    ) {
        return new Spy(
            $callback,
            $useTraversableSpies,
            $useGeneratorSpies,
            $this->idSequencer->next(),
            $this->callFactory,
            $this->traversableSpyFactory
        );
    }

    private static $instance;
    private $idSequencer;
    private $callFactory;
    private $traversableSpyFactory;
}
