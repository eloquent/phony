<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Test;

use Eloquent\Phony\Call\Event\Factory\CallEventFactory;
use Eloquent\Phony\Sequencer\Sequencer;

class TestCallEventFactory extends CallEventFactory
{
    public function __construct()
    {
        parent::__construct(new Sequencer(), new TestClock());
    }

    public function reset()
    {
        $this->sequencer()->reset();
        $this->clock()->reset();
    }
}
