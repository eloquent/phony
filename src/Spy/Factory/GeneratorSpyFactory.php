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

use Eloquent\Phony\Call\CallInterface;
use Eloquent\Phony\Call\Event\Factory\CallEventFactory;
use Eloquent\Phony\Call\Event\Factory\CallEventFactoryInterface;
use Eloquent\Phony\Feature\FeatureDetector;
use Eloquent\Phony\Feature\FeatureDetectorInterface;
use Generator;
use InvalidArgumentException;
use Traversable;

/**
 * Creates generator spies.
 */
class GeneratorSpyFactory implements TraversableSpyFactoryInterface
{
    /**
     * Get the static instance of this factory.
     *
     * @return TraversableSpyFactoryInterface The static factory.
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Construct a new generator spy factory.
     *
     * @param CallEventFactoryInterface|null $callEventFactory The call event factory to use.
     * @param FeatureDetectorInterface|null  $featureDetector  The feature detector to use.
     */
    public function __construct(
        CallEventFactoryInterface $callEventFactory = null,
        FeatureDetectorInterface $featureDetector = null
    ) {
        if (!$callEventFactory) {
            $callEventFactory = CallEventFactory::instance();
        }
        if (!$featureDetector) {
            $featureDetector = FeatureDetector::instance();
        }

        $this->callEventFactory = $callEventFactory;
        $this->featureDetector = $featureDetector;

        $this->isGeneratorReturnSupported = $featureDetector
            ->isSupported('generator.return');
        $this->isHhvm = $featureDetector->isSupported('runtime.hhvm');
    }

    /**
     * Get the call event factory.
     *
     * @return CallEventFactoryInterface The call event factory.
     */
    public function callEventFactory()
    {
        return $this->callEventFactory;
    }

    /**
     * Get the feature detector.
     *
     * @return FeatureDetectorInterface The feature detector.
     */
    public function featureDetector()
    {
        return $this->featureDetector;
    }

    /**
     * Create a new traversable spy.
     *
     * @param CallInterface     $call        The call from which the traversable originated.
     * @param Traversable|array $traversable The traversable.
     *
     * @return Traversable              The newly created traversable spy.
     * @throws InvalidArgumentException If the supplied traversable is invalid.
     */
    public function create(CallInterface $call, $traversable)
    {
        if (!$traversable instanceof Generator) {
            if (is_object($traversable)) {
                $type = var_export(get_class($traversable), true);
            } else {
                $type = gettype($traversable);
            }

            throw new InvalidArgumentException(
                sprintf('Unsupported traversable of type %s.', $type)
            );
        }

        if ($this->isHhvm) { // @codeCoverageIgnoreStart
            if ($this->isGeneratorReturnSupported) {
                return
                    GeneratorSpyFactoryDetailHhvmWithReturn::createGeneratorSpy(
                        $call,
                        $traversable,
                        $this->callEventFactory
                    );
            }

            return GeneratorSpyFactoryDetailHhvm::createGeneratorSpy(
                $call,
                $traversable,
                $this->callEventFactory
            );
        } elseif ($this->isGeneratorReturnSupported) {
            return GeneratorSpyFactoryDetailPhpWithReturn::createGeneratorSpy(
                $call,
                $traversable,
                $this->callEventFactory
            );
        } // @codeCoverageIgnoreEnd

        return GeneratorSpyFactoryDetailPhp::createGeneratorSpy(
            $call,
            $traversable,
            $this->callEventFactory
        );
    }

    private static $instance;
    private $featureDetector;
    private $callEventFactory;
    private $isGeneratorReturnSupported;
    private $isHhvm;
}
