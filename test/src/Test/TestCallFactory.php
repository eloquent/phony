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

use Eloquent\Phony\Call\Factory\CallFactory;

class TestCallFactory extends CallFactory
{
    public function __construct()
    {
        parent::__construct(new TestCallEventFactory());
    }

    public function reset()
    {
        $this->eventFactory()->reset();
    }
}
