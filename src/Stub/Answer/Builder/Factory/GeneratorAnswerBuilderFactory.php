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
            self::$instance = new self(
                InvocableInspector::instance(),
                Invoker::instance(),
                FeatureDetector::instance()
            );
        }

        return self::$instance;
    }

    /**
     * Construct a new generator answer builder factory.
     *
     * @param InvocableInspectorInterface $invocableInspector The invocable inspector to use.
     * @param InvokerInterface            $invoker            The invoker to use.
     * @param FeatureDetectorInterface    $featureDetector    The feature detector to use.
     */
    public function __construct(
        InvocableInspectorInterface $invocableInspector,
        InvokerInterface $invoker,
        FeatureDetectorInterface $featureDetector
    ) {
        $this->invocableInspector = $invocableInspector;
        $this->invoker = $invoker;
        $this->featureDetector = $featureDetector;

        $this->isGeneratorReturnSupported =
            $featureDetector->isSupported('generator.return');
    }

    /**
     * Create a generator answer builder for the supplied stub.
     *
     * @param StubInterface $stub The stub.
     *
     * @return GeneratorAnswerBuilderInterface The newly created builder.
     */
    public function create(StubInterface $stub)
    {
        return new GeneratorAnswerBuilder(
            $stub,
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
