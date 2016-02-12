<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Matcher;

use Athletic\AthleticEvent;

class EqualToMatcherVsRecursiveEvent extends AthleticEvent
{
    protected function generateNestedArray($depth)
    {
        $array = array('end');

        for ($i = 0; $i < $depth; ++$i) {
            $array = array('nested' => $array);
        }

        return $array;
    }

    protected function setUp()
    {
        require_once __DIR__ . '/RecursiveEqualToMatcher.php';

        // Depth must be lower than PHP's stack depth.
        $this->array1 = $this->generateNestedArray(200);
        $this->array2 = $this->generateNestedArray(200);

        $this->iterative = new EqualToMatcher($this->array1);
        $this->recursive = new RecursiveEqualToMatcher($this->array1);
    }

    /**
     * @iterations 10
     */
    public function iterative()
    {
        $this->iterative->matches(
            $this->array2
        );
    }

    /**
     * @iterations 10
     */
    public function recursive()
    {
        $this->recursive->matches(
            $this->array2
        );
    }
}
