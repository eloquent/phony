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
use Eloquent\Phony\Exporter\InlineExporter;

class EqualToMatcherNestedArrayEvent extends AthleticEvent
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
        $this->array1 = $this->generateNestedArray(1000);
        $this->array2 = $this->generateNestedArray(1000);

        $this->exporter = InlineExporter::instance();
        $this->subject = new EqualToMatcher($this->array1, $this->exporter);
    }

    /**
     * @iterations 10
     */
    public function identicalArray()
    {
        $this->subject->matches(
            $this->array1
        );
    }

    /**
     * @iterations 10
     */
    public function equalArray()
    {
        $this->subject->matches(
            $this->array2
        );
    }
}
