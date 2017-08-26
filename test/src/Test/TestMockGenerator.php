<?php

namespace Eloquent\Phony\Test;

use Eloquent\Phony\Mock\Builder\MockDefinition;
use Eloquent\Phony\Mock\MockGenerator;
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Reflection\FunctionSignatureInspectorFactory;
use Eloquent\Phony\Sequencer\Sequencer;

class TestMockGenerator extends MockGenerator
{
    public function __construct($source)
    {
        $this->source = $source;

        parent::__construct(
            Sequencer::sequence('mock-class-label'),
            FunctionSignatureInspectorFactory::create(),
            FeatureDetector::instance()
        );
    }

    public function generate(
        MockDefinition $definition,
        string $className = null
    ): string {
        return $this->source;
    }

    private $source;
}
