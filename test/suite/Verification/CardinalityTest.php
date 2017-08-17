<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2017 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Verification;

use PHPUnit\Framework\TestCase;

class CardinalityTest extends TestCase
{
    protected function setUp()
    {
        $this->minimum = 111;
        $this->maximum = 222;
        $this->isAlways = true;
        $this->subject = new Cardinality($this->minimum, $this->maximum, $this->isAlways);
    }

    public function testConstructor()
    {
        $this->assertSame($this->minimum, $this->subject->minimum());
        $this->assertSame($this->maximum, $this->subject->maximum());
        $this->assertFalse($this->subject->isNever());
        $this->assertSame($this->isAlways, $this->subject->isAlways());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new Cardinality();

        $this->assertSame(1, $this->subject->minimum());
        $this->assertNull($this->subject->maximum());
        $this->assertFalse($this->subject->isNever());
        $this->assertFalse($this->subject->isAlways());
    }

    public function testConstructorFailureNegativeMin()
    {
        $this->expectException('Eloquent\Phony\Verification\Exception\InvalidCardinalityStateException');
        new Cardinality(-1);
    }

    public function testConstructorFailureNegativeMax()
    {
        $this->expectException('Eloquent\Phony\Verification\Exception\InvalidCardinalityStateException');
        new Cardinality(0, -1);
    }

    public function testConstructorFailureInvalidMinMax()
    {
        $this->expectException('Eloquent\Phony\Verification\Exception\InvalidCardinalityStateException');
        new Cardinality($this->maximum, $this->minimum);
    }

    public function testConstructorFailureInvalidIsAlways()
    {
        $this->expectException('Eloquent\Phony\Verification\Exception\InvalidCardinalityStateException');
        new Cardinality(0, 0, true);
    }

    public function testConstructorFailureInvalidIsAny()
    {
        $this->expectException('Eloquent\Phony\Verification\Exception\InvalidCardinalityStateException');
        new Cardinality(0, null);
    }

    public function testIsNever()
    {
        $this->subject = new Cardinality(0, 0);

        $this->assertTrue($this->subject->isNever());
    }

    public function testSetIsAlways()
    {
        $this->subject->setIsAlways(!$this->isAlways);

        $this->assertSame(!$this->isAlways, $this->subject->isAlways());
    }

    public function testSetIsAlwaysFailure()
    {
        $this->subject = new Cardinality(0, 0);

        $this->expectException('Eloquent\Phony\Verification\Exception\InvalidCardinalityStateException');
        $this->subject->setIsAlways(true);
    }

    public function matchesData()
    {
        //                                         minimum maximum isAlways count  maximumCount expected
        return [
            'Less than minimum'           => [1,      null,   false,   0,     1,           false],
            'Equal to minimum'            => [1,      null,   false,   1,     1,           true],
            'Greater than minimum'        => [1,      null,   false,   2,     1,           true],

            'Less than maximum'           => [null,   1,      false,   0,     1,           true],
            'Equal to maximum'            => [null,   1,      false,   1,     1,           true],
            'Greater than maximum'        => [null,   1,      false,   2,     1,           false],

            'Less than bounds minimum'    => [1,      3,      false,   0,     1,           false],
            'Equal to bounds minimum'     => [1,      3,      false,   1,     1,           true],
            'Within bounds'               => [1,      3,      false,   2,     1,           true],
            'Equal to bounds maximum'     => [1,      3,      false,   3,     1,           true],
            'Greater than bounds maximum' => [1,      3,      false,   4,     1,           false],

            'Boolean true'                => [1,      null,   false,   true,  1,           true],
            'Boolean false'               => [1,      null,   false,   false, 1,           false],
            'Boolean true with never'     => [null,   0,      false,   true,  1,           false],
            'Boolean false with never'    => [null,   0,      false,   false, 1,           true],

            'Always'                      => [null,   3,      true,    2,     2,           true],
            'Not always'                  => [null,   3,      true,    2,     3,           false],
        ];
    }

    /**
     * @dataProvider matchesData
     */
    public function testMatches($minimum, $maximum, $isAlways, $count, $maximumCount, $expected)
    {
        $this->subject = new Cardinality($minimum, $maximum, $isAlways);

        $this->assertSame($expected, $this->subject->matches($count, $maximumCount));
    }

    public function testAssertSingular()
    {
        $this->subject = new Cardinality();

        $this->assertSame($this->subject, $this->subject->assertSingular());
    }

    public function testAssertSingularFailureMinimum()
    {
        $this->subject = new Cardinality(2);

        $this->expectException('Eloquent\Phony\Verification\Exception\InvalidSingularCardinalityException');
        $this->subject->assertSingular();
    }

    public function testAssertSingularFailureMaximum()
    {
        $this->subject = new Cardinality(0, 2);

        $this->expectException('Eloquent\Phony\Verification\Exception\InvalidSingularCardinalityException');
        $this->subject->assertSingular();
    }
}
