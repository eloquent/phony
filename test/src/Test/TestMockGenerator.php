<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

use Eloquent\Phony\Mock\Builder\MockDefinition;
use Eloquent\Phony\Mock\MockGenerator;
use Eloquent\Phony\Reflection\FunctionSignatureInspector;
use Eloquent\Phony\Sequencer\Sequencer;

class TestMockGenerator extends MockGenerator
{
    public function __construct($source)
    {
        $this->source = $source;

        parent::__construct(
            Sequencer::sequence('mock-class-label'),
            FunctionSignatureInspector::instance()
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
