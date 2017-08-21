<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2017 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy;

use Eloquent\Phony\Call\Call;
use Eloquent\Phony\Call\Event\CallEventFactory;
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Spy\Detail\GeneratorSpyFactoryDetailHhvm;
use Eloquent\Phony\Spy\Detail\GeneratorSpyFactoryDetailPhp;
use Generator;

/**
 * Creates generator spies.
 */
class GeneratorSpyFactory
{
    /**
     * Get the static instance of this factory.
     *
     * @return GeneratorSpyFactory The static factory.
     */
    public static function instance(): self
    {
        if (!self::$instance) {
            self::$instance = new self(
                CallEventFactory::instance(),
                FeatureDetector::instance()
            );
        }

        return self::$instance;
    }

    /**
     * Construct a new generator spy factory.
     *
     * @param CallEventFactory $callEventFactory The call event factory to use.
     * @param FeatureDetector  $featureDetector  The feature detector to use.
     */
    public function __construct(
        CallEventFactory $callEventFactory,
        FeatureDetector $featureDetector
    ) {
        $this->callEventFactory = $callEventFactory;
        $this->isHhvm = $featureDetector->isSupported('runtime.hhvm');
    }

    /**
     * Create a new generator spy.
     *
     * @param Call      $call      The call from which the generator originated.
     * @param Generator $generator The generator.
     *
     * @return Generator The newly created generator spy.
     */
    public function create(Call $call, Generator $generator): Generator
    {
        // @codeCoverageIgnoreStart
        if ($this->isHhvm) {
            $spy = GeneratorSpyFactoryDetailHhvm::createGeneratorSpy(
                $call,
                $generator,
                $this->callEventFactory
            );
        } else {
            // @codeCoverageIgnoreEnd
            $spy = GeneratorSpyFactoryDetailPhp::createGeneratorSpy(
                $call,
                $generator,
                $this->callEventFactory
            );
        }

        $spy->_phonySubject = $generator;

        return $spy;
    }

    private static $instance;
    private $callEventFactory;
    private $isHhvm;
}
