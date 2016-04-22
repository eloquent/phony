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

use Eloquent\Phony\Clock\Clock;

class TestClock implements Clock
{
    public function time()
    {
        return ($this->time += 1.0);
    }

    public function setTime($time)
    {
        $this->time = $time - 1.0;
    }

    public function reset()
    {
        $this->time = -1.0;
    }

    private $time = -1.0;
}
