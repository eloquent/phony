<?php

declare(strict_types=1);

namespace Eloquent\Phony\Stub\Answer\Builder;

use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Stub\Stub;

/**
 * Creates generator answer builders.
 */
class GeneratorAnswerBuilderFactory
{
    /**
     * Get the static instance of this factory.
     *
     * @return GeneratorAnswerBuilderFactory The static factory.
     */
    public static function instance(): self
    {
        if (!self::$instance) {
            self::$instance = new self(
                InvocableInspector::instance(),
                Invoker::instance()
            );
        }

        return self::$instance;
    }

    /**
     * Construct a new generator answer builder factory.
     *
     * @param InvocableInspector $invocableInspector The invocable inspector to use.
     * @param Invoker            $invoker            The invoker to use.
     */
    public function __construct(
        InvocableInspector $invocableInspector,
        Invoker $invoker
    ) {
        $this->invocableInspector = $invocableInspector;
        $this->invoker = $invoker;
    }

    /**
     * Create a generator answer builder for the supplied stub.
     *
     * @param Stub $stub The stub.
     *
     * @return GeneratorAnswerBuilder The newly created builder.
     */
    public function create(Stub $stub): GeneratorAnswerBuilder
    {
        return new GeneratorAnswerBuilder(
            $stub,
            $this->invocableInspector,
            $this->invoker
        );
    }

    private static $instance;
    private $invocableInspector;
    private $invoker;
}
