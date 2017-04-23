<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2017 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Test;

use Eloquent\Phony\Mock\Builder\MockDefinition;
use Eloquent\Phony\Mock\MockGenerator;
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Reflection\FunctionSignatureInspector;
use Eloquent\Phony\Sequencer\Sequencer;

class TestMockGenerator extends MockGenerator
{
    public function __construct($source)
    {
        $this->source = $source;

        parent::__construct(
            Sequencer::sequence('mock-class-label'),
            FunctionSignatureInspector::instance(),
            FeatureDetector::instance()
        );
    }

    public function generate(MockDefinition $definition, $className = null)
    {
        return $this->source;
    }

    private $source;
}
