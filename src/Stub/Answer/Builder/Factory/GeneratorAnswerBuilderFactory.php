<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub\Answer\Builder\Factory;

use Eloquent\Phony\Feature\FeatureDetector;
use Eloquent\Phony\Feature\FeatureDetectorInterface;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\InvocableInspectorInterface;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Invocation\InvokerInterface;
use Eloquent\Phony\Stub\Answer\Builder\GeneratorAnswerBuilder;
use Eloquent\Phony\Stub\StubInterface;

/**
 * Creates generator answer builders.
 *
 * @api
 */
class GeneratorAnswerBuilderFactory implements
    GeneratorAnswerBuilderFactoryInterface
{
    /**
     * Get the static instance of this factory.
     *
     * @return GeneratorAnswerBuilderFactoryInterface The static factory.
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Construct a new generator answer builder factory.
     *
     * @param InvocableInspectorInterface|null $invocableInspector The invocable inspector to use.
     * @param InvokerInterface|null            $invoker            The invoker to use.
     * @param FeatureDetectorInterface|null    $featureDetector    The feature detector to use.
     */
    public function __construct(
        InvocableInspectorInterface $invocableInspector = null,
        InvokerInterface $invoker = null,
        FeatureDetectorInterface $featureDetector = null
    ) {
        if (!$invocableInspector) {
            $invocableInspector = InvocableInspector::instance();
        }
        if (!$invoker) {
            $invoker = Invoker::instance();
        }
        if (!$featureDetector) {
            $featureDetector = FeatureDetector::instance();
        }

        $this->invocableInspector = $invocableInspector;
        $this->invoker = $invoker;
        $this->featureDetector = $featureDetector;

        $this->isGeneratorReturnSupported =
            $featureDetector->isSupported('generator.return');
    }

    /**
     * Get the invocable inspector.
     *
     * @return InvocableInspectorInterface The invocable inspector.
     */
    public function invocableInspector()
    {
        return $this->invocableInspector;
    }

    /**
     * Get the invoker.
     *
     * @return InvokerInterface The invoker.
     */
    public function invoker()
    {
        return $this->invoker;
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
     * Create a generator answer builder for the supplied stub.
     *
     * @param StubInterface $stub   The stub.
     * @param array         $values An array of keys and values to yield.
     *
     * @return GeneratorAnswerBuilderInterface The newly created builder.
     */
    public function create(StubInterface $stub, array $values = array())
    {
        return new GeneratorAnswerBuilder(
            $stub,
            $values,
            $this->isGeneratorReturnSupported,
            $this->invocableInspector,
            $this->invoker
        );
    }

    private static $instance;
    private $invocableInspector;
    private $invoker;
    private $featureDetector;
    private $isGeneratorReturnSupported;
}
