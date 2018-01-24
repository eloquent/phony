<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Call\Event\CalledEvent;
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

    public function sequencer(): Sequencer
    {
        return $this->sequencer;
    }

    public function clock(): TestClock
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
    ): CalledEvent {
        if (!$callback) {
            $callback = function () {};
        }
        if (!$arguments) {
            $arguments = new Arguments([]);
        }

        return parent::createCalled($callback, $arguments);
    }

    private $sequencer;
    private $clock;
}
