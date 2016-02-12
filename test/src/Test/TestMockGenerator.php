<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Test;

use Eloquent\Phony\Mock\Builder\Definition\MockDefinitionInterface;
use Eloquent\Phony\Mock\Generator\MockGenerator;

class TestMockGenerator extends MockGenerator
{
    public function __construct($source)
    {
        $this->source = $source;

        parent::__construct();
    }

    public function generate(
        MockDefinitionInterface $definition,
        $className = null
    ) {
        return $this->source;
    }

    private $source;
}
