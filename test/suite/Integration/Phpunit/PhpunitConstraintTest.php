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

use Exception;
use PHPUnit_Framework_TestCase;
use SebastianBergmann\Comparator\ComparisonFailure;

class PhpunitConstraintTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $self = $this;
        $this->expected = array('valueA', 'valueB');
        $this->description = 'description';
        $this->matchesCallback = function ($subject) use ($self) {
            return $self->expected === $subject;
        };
        $this->differenceCallback = function ($subject) use ($self) {
            return new ComparisonFailure(
                $self->expected,
                $subject,
                var_export($self->expected, true),
                var_export($subject, true)
            );
        };
        $this->subject = new PhpunitConstraint($this->description, $this->matchesCallback, $this->differenceCallback);

        $this->unexpected = array('valueB', 'valueC');
    }

    public function testConstructor()
    {
        $this->assertSame($this->description, $this->subject->description());
        $this->assertSame($this->description, $this->subject->toString());
        $this->assertSame($this->description, strval($this->subject));
        $this->assertSame($this->matchesCallback, $this->subject->matchesCallback());
        $this->assertSame($this->differenceCallback, $this->subject->differenceCallback());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new PhpunitConstraint($this->description, $this->matchesCallback);

        $this->assertNull($this->subject->differenceCallback());
    }

    public function testEvaulate()
    {
        $exception = null;
        try {
            $this->assertNull($this->subject->evaluate($this->expected));
            $this->assertTrue($this->subject->evaluate($this->expected, null, true));
            $this->assertFalse($this->subject->evaluate($this->unexpected, null, true));
        } catch (Exception $exception) {}

        $this->assertNull($exception);
    }

    public function testEvaluateFailure()
    {
        $exception = null;
        try {
            $this->subject->evaluate($this->unexpected, 'message');
        } catch (Exception $exception) {}
        $expectedMessage = <<<'EOD'
message
Failed asserting that Array &0 (
    0 => 'valueB'
    1 => 'valueC'
) description.
EOD;
        $expectedDifference = <<<'EOD'

--- Expected
+++ Actual
@@ @@
 array (
-  0 => 'valueA',
-  1 => 'valueB',
+  0 => 'valueB',
+  1 => 'valueC',
 )

EOD;

        $this->assertInstanceOf('PHPUnit_Framework_ExpectationFailedException', $exception);
        $this->assertSame($expectedMessage, $exception->getMessage());
        $this->assertInstanceOf('SebastianBergmann\Comparator\ComparisonFailure', $exception->getComparisonFailure());
        $this->assertSame($expectedDifference, $exception->getComparisonFailure()->getDiff());
    }

    public function testEvaluateFailureWithoutDiff()
    {
        $this->subject = new PhpunitConstraint($this->description, $this->matchesCallback);
        $exception = null;
        try {
            $this->subject->evaluate($this->unexpected, 'message');
        } catch (Exception $exception) {}
        $expectedMessage = <<<'EOD'
message
Failed asserting that Array &0 (
    0 => 'valueB'
    1 => 'valueC'
) description.
EOD;

        $this->assertInstanceOf('PHPUnit_Framework_ExpectationFailedException', $exception);
        $this->assertSame($expectedMessage, $exception->getMessage());
        $this->assertNull($exception->getComparisonFailure());
    }
}
