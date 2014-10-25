<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Test;

use Eloquent\Phony\Mock\Builder\Definition\MockDefinitionInterface;
use Eloquent\Phony\Mock\Generator\MockGeneratorInterface;

class TestMockGenerator implements MockGeneratorInterface
{
    public function __construct($source)
    {
        $this->source = $source;
    }

    public function generate(MockDefinitionInterface $definition)
    {
        return $this->source;
    }

    private $source;
}
