<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Builder\Factory;

use Eloquent\Phony\Mock\Builder\MockBuilder;
use Eloquent\Phony\Mock\Builder\MockBuilderInterface;
use Eloquent\Phony\Mock\Factory\MockFactory;
use Eloquent\Phony\Mock\Factory\MockFactoryInterface;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Sequencer\SequencerInterface;

/**
 * Creates mock builders.
 *
 * @internal
 */
class MockBuilderFactory implements MockBuilderFactoryInterface
{
    /**
     * Get the static instance of this factory.
     *
     * @return MockBuilderFactoryInterface The static factory.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Construct a new mock builder factory.
     *
     * @param SequencerInterface|null   $idSequencer The identifier sequencer to use.
     * @param MockFactoryInterface|null $mockFactory The mock factory to use.
     */
    public function __construct(
        SequencerInterface $idSequencer = null,
        MockFactoryInterface $mockFactory = null
    ) {
        if (null === $idSequencer) {
            $idSequencer = new Sequencer();
        }
        if (null === $mockFactory) {
            $mockFactory = MockFactory::instance();
        }

        $this->idSequencer = $idSequencer;
        $this->mockFactory = $mockFactory;
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
     * Get the mock factory.
     *
     * @return MockFactoryInterface The mock factory.
     */
    public function mockFactory()
    {
        return $this->mockFactory;
    }

    /**
     * Create a new mock builder.
     *
     * @param array<string|object>|string|object|null $types      The types to mock.
     * @param array|object|null                       $definition The definition.
     * @param string|null                             $className  The class name.
     *
     * @return MockBuilderInterface The mock builder.
     */
    public function create(
        $types = null,
        $definition = null,
        $className = null
    ) {
        return new MockBuilder(
            $types,
            $definition,
            $className,
            $this->idSequencer->next(),
            $this->mockFactory
        );
    }

    private static $instance;
    private $idSequencer;
    private $mockFactory;
}
