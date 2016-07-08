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

use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Stub\FunctionHookGenerator;

class TestFunctionHookGenerator extends FunctionHookGenerator
{
    public function __construct($source)
    {
        $this->source = $source;

        parent::__construct(FeatureDetector::instance());
    }

    public function generateHook($name, array $signature)
    {
        return $this->source;
    }

    private $source;
}
