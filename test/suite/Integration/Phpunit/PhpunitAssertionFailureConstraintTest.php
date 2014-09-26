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

use Eloquent\Phony\Test\TestAssertionException;
use Exception;
use PHPUnit_Framework_TestCase;

class PhpunitAssertionFailureConstraintTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->failure = new TestAssertionException('message');
        $this->subject = new PhpunitAssertionFailureConstraint($this->failure);
    }

    public function testConstructor()
    {
        $this->assertSame($this->failure, $this->subject->failure());
        $this->assertSame($this->failure->getMessage(), $this->subject->toString());
    }

    public function testEvaluate()
    {
        $exception = null;
        try {
            $this->subject->evaluate(null);
        } catch (Exception $exception) {}

        $this->assertEquals(new PhpunitAssertionException($this->failure), $exception);
    }
}
