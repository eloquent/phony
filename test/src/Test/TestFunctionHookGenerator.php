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

use Eloquent\Phony\Hook\FunctionHookGenerator;

class TestFunctionHookGenerator extends FunctionHookGenerator
{
    public function __construct($source)
    {
        $this->source = $source;
    }

    public function generateHook($name, $namespace, array $signature)
    {
        return $this->source;
    }

    private $source;
}
