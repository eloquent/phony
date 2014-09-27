<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Integration\Phpunit;

use PHPUnit_Framework_TestCase;
use PHPUnit_Framework_ExpectationFailedException;

class PhpunitAssertionFailureConstraintTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->description = 'description';
        $this->subject = new PhpunitAssertionFailureConstraint($this->description);
    }

    public function testConstructor()
    {
        $this->assertSame($this->description, $this->subject->toString());
    }

    public function testEvaluate()
    {
        $exception = null;
        try {
            $this->subject->evaluate(null);
        } catch (PHPUnit_Framework_ExpectationFailedException $exception) {}

        $this->assertEquals(new PHPUnit_Framework_ExpectationFailedException($this->description), $exception);
    }
}
