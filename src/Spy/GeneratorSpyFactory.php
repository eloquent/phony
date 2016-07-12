<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy;

use Eloquent\Phony\Call\Call;
use Eloquent\Phony\Call\Event\CallEventFactory;
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Spy\Detail\GeneratorSpyFactoryDetailHhvm;
use Eloquent\Phony\Spy\Detail\GeneratorSpyFactoryDetailHhvmWithReturn;
use Eloquent\Phony\Spy\Detail\GeneratorSpyFactoryDetailPhp;
use Eloquent\Phony\Spy\Detail\GeneratorSpyFactoryDetailPhpWithReturn;
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
    public static function instance()
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
        $this->featureDetector = $featureDetector;

        $this->isGeneratorReturnSupported = $featureDetector
            ->isSupported('generator.return');
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
    public function create(Call $call, Generator $generator)
    {
        if ($this->isHhvm) {
            // @codeCoverageIgnoreStart
            if ($this->isGeneratorReturnSupported) {
                return
                    GeneratorSpyFactoryDetailHhvmWithReturn::createGeneratorSpy(
                        $call,
                        $generator,
                        $this->callEventFactory
                    );
            }

            return GeneratorSpyFactoryDetailHhvm::createGeneratorSpy(
                $call,
                $generator,
                $this->callEventFactory
            );
            // @codeCoverageIgnoreEnd
        } elseif ($this->isGeneratorReturnSupported) {
            return GeneratorSpyFactoryDetailPhpWithReturn::createGeneratorSpy(
                $call,
                $generator,
                $this->callEventFactory
            );
        }

        // @codeCoverageIgnoreStart
        return GeneratorSpyFactoryDetailPhp::createGeneratorSpy(
            $call,
            $generator,
            $this->callEventFactory
        );
        // @codeCoverageIgnoreEnd
    }

    private static $instance;
    private $featureDetector;
    private $callEventFactory;
    private $isGeneratorReturnSupported;
    private $isHhvm;
}
