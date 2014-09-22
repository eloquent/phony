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
use Eloquent\Phony\Clock\ClockInterface;
use Eloquent\Phony\Clock\SystemClock;
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
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Construct a new spy factory.
     *
     * @param SequencerInterface|null   $sequencer   The sequencer to use.
     * @param ClockInterface|null       $clock       The clock to use.
     * @param CallFactoryInterface|null $callFactory The call factory to use.
     */
    public function __construct(
        SequencerInterface $sequencer = null,
        ClockInterface $clock = null,
        CallFactoryInterface $callFactory = null
    ) {
        if (null === $sequencer) {
            $sequencer = new Sequencer();
        }
        if (null === $clock) {
            $clock = SystemClock::instance();
        }
        if (null === $callFactory) {
            $callFactory = CallFactory::instance();
        }

        $this->sequencer = $sequencer;
        $this->clock = $clock;
        $this->callFactory = $callFactory;
    }

    /**
     * Get the sequencer.
     *
     * @return SequencerInterface The sequencer.
     */
    public function sequencer()
    {
        return $this->sequencer;
    }

    /**
     * Get the clock.
     *
     * @return ClockInterface The clock.
     */
    public function clock()
    {
        return $this->clock;
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
     * Create a new spy.
     *
     * @param callable|null $subject The subject, or null to create an unbound spy.
     *
     * @return SpyInterface The newly created spy.
     */
    public function create($subject = null)
    {
        return new Spy(
            $subject,
            $this->callFactory,
            $this->sequencer,
            $this->clock
        );
    }

    private static $instance;
    private $sequencer;
    private $clock;
    private $callFactory;
}
