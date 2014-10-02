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

use Eloquent\Phony\Clock\ClockInterface;

class TestClock implements ClockInterface
{
    public function time()
    {
        $currentTime = $this->time;
        $this->time += 1.0;

        return $currentTime;
    }

    private $time = 0.123;
}
