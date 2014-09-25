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

use Eloquent\Phony\Comparator\ComparatorInterface;

class TestComparator implements ComparatorInterface
{
    public function __construct($comparator)
    {
        $this->comparator = $comparator;
        $this->calls = array();
    }

    public function calls()
    {
        return $this->calls;
    }

    public function compare($left, $right)
    {
        $this->calls[] = array($left, $right);

        return call_user_func($this->comparator, $left, $right);
    }

    public function __invoke($left, $right)
    {
        return $this->compare($left, $right);
    }

    private $comparator;
    private $calls;
}
