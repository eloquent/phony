<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

use Eloquent\Phony\Phpunit as x;

class FunctionalTestWithGenerators extends PHPUnit_Framework_TestCase
{
    public function testYieldByReferenceSupport()
    {
        $reference = null;
        $spy = x\spy(
            function &() use (&$reference) {
                yield $reference;
            }
        );

        foreach ($spy() as &$value) {
            $value = 'a';
        }

        $this->assertNull($reference);
    }
}
