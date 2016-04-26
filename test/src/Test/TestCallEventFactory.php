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

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Call\Event\CallEventFactory;
use Eloquent\Phony\Sequencer\Sequencer;

class TestCallEventFactory extends CallEventFactory
{
    public function __construct()
    {
        $this->sequencer = new Sequencer();
        $this->clock = new TestClock();

        parent::__construct($this->sequencer, $this->clock);
    }

    public function sequencer()
    {
        return $this->sequencer;
    }

    public function clock()
    {
        return $this->clock;
    }

    public function reset()
    {
        $this->sequencer->reset();
        $this->clock->reset();
    }

    public function createCalled(
        $callback = null,
        Arguments $arguments = null
    ) {
        if (!$callback) {
            $callback = function () {};
        }
        if (!$arguments) {
            $arguments = new Arguments(array());
        }

        return parent::createCalled($callback, $arguments);
    }

    private $sequencer;
    private $clock;
}
