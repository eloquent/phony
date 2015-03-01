<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy\Factory;

use Eloquent\Phony\Call\Factory\CallFactory;
use Eloquent\Phony\Call\Factory\CallFactoryInterface;
use Eloquent\Phony\Collection\IndexNormalizer;
use Eloquent\Phony\Collection\IndexNormalizerInterface;
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
     * @param SequencerInterface|null             $labelSequencer        The label sequencer to use.
     * @param IndexNormalizerInterface|null       $indexNormalizer       The index normalizer to use.
     * @param CallFactoryInterface|null           $callFactory           The call factory to use.
     * @param TraversableSpyFactoryInterface|null $generatorSpyFactory   The generator spy factory to use.
     * @param TraversableSpyFactoryInterface|null $traversableSpyFactory The traversable spy factory to use.
     */
    public function __construct(
        SequencerInterface $labelSequencer = null,
        IndexNormalizerInterface $indexNormalizer = null,
        CallFactoryInterface $callFactory = null,
        TraversableSpyFactoryInterface $generatorSpyFactory = null,
        TraversableSpyFactoryInterface $traversableSpyFactory = null
    ) {
        if (null === $labelSequencer) {
            $labelSequencer = Sequencer::sequence('spy-label');
        }
        if (null === $indexNormalizer) {
            $indexNormalizer = IndexNormalizer::instance();
        }
        if (null === $callFactory) {
            $callFactory = CallFactory::instance();
        }
        if (null === $generatorSpyFactory) {
            $generatorSpyFactory = GeneratorSpyFactory::instance();
        }
        if (null === $traversableSpyFactory) {
            $traversableSpyFactory = TraversableSpyFactory::instance();
        }

        $this->labelSequencer = $labelSequencer;
        $this->indexNormalizer = $indexNormalizer;
        $this->callFactory = $callFactory;
        $this->generatorSpyFactory = $generatorSpyFactory;
        $this->traversableSpyFactory = $traversableSpyFactory;
    }

    /**
     * Get the label sequencer.
     *
     * @return SequencerInterface The label sequencer.
     */
    public function labelSequencer()
    {
        return $this->labelSequencer;
    }

    /**
     * Get the index normalizer.
     *
     * @return IndexNormalizerInterface The index normalizer.
     */
    public function indexNormalizer()
    {
        return $this->indexNormalizer;
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
     * Get the generator spy factory.
     *
     * @return TraversableSpyFactoryInterface The generator spy factory.
     */
    public function generatorSpyFactory()
    {
        return $this->generatorSpyFactory;
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
     * @param boolean|null  $useGeneratorSpies   True if generator spies should be used.
     * @param boolean|null  $useTraversableSpies True if traversable spies should be used.
     *
     * @return SpyInterface The newly created spy.
     */
    public function create(
        $callback = null,
        $useGeneratorSpies = null,
        $useTraversableSpies = null
    ) {
        return new Spy(
            $callback,
            strval($this->labelSequencer->next()),
            $useGeneratorSpies,
            $useTraversableSpies,
            $this->indexNormalizer,
            $this->callFactory,
            $this->generatorSpyFactory,
            $this->traversableSpyFactory
        );
    }

    private static $instance;
    private $labelSequencer;
    private $indexNormalizer;
    private $callFactory;
    private $generatorSpyFactory;
    private $traversableSpyFactory;
}
