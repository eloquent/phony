<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Verification\Cardinality;

use PHPUnit_Framework_TestCase;

class CardinalityTest extends PHPUnit_Framework_TestCase
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

        $this->assertSame(0, $this->subject->minimum());
        $this->assertNull($this->subject->maximum());
        $this->assertFalse($this->subject->isNever());
        $this->assertFalse($this->subject->isAlways());
    }

    public function testConstructorFailureNegativeMin()
    {
        $this->setExpectedException('Eloquent\Phony\Verification\Exception\InvalidCardinalityException');
        new Cardinality(-1);
    }

    public function testConstructorFailureNegativeMax()
    {
        $this->setExpectedException('Eloquent\Phony\Verification\Exception\InvalidCardinalityException');
        new Cardinality(null, -1);
    }

    public function testConstructorFailureInvalidMinMax()
    {
        $this->setExpectedException('Eloquent\Phony\Verification\Exception\InvalidCardinalityException');
        new Cardinality($this->maximum, $this->minimum);
    }

    public function testConstructorFailureInvalidIsAlways()
    {
        $this->setExpectedException('Eloquent\Phony\Verification\Exception\InvalidCardinalityException');
        new Cardinality(null, 0, true);
    }

    public function testIsNever()
    {
        $this->subject = new Cardinality(null, 0);

        $this->assertTrue($this->subject->isNever());
    }

    public function testSetIsAlways()
    {
        $this->subject->setIsAlways(!$this->isAlways);

        $this->assertSame(!$this->isAlways, $this->subject->isAlways());
    }

    public function testSetIsAlwaysFailure()
    {
        $this->subject = new Cardinality(null, 0);

        $this->setExpectedException('Eloquent\Phony\Verification\Exception\InvalidCardinalityException');
        $this->subject->setIsAlways(true);
    }

    public function matchesData()
    {
        //                                         minimum maximum isAlways count  maximumCount expected
        return array(
            'Less than minimum'           => array(1,      null,   false,   0,     null,        false),
            'Equal to minimum'            => array(1,      null,   false,   1,     null,        true),
            'Greater than minimum'        => array(1,      null,   false,   2,     null,        true),

            'Less than maximum'           => array(null,   1,      false,   0,     null,        true),
            'Equal to maximum'            => array(null,   1,      false,   1,     null,        true),
            'Greater than maximum'        => array(null,   1,      false,   2,     null,        false),

            'Less than bounds minimum'    => array(1,      3,      false,   0,     null,        false),
            'Equal to bounds minimum'     => array(1,      3,      false,   1,     null,        true),
            'Within bounds'               => array(1,      3,      false,   2,     null,        true),
            'Equal to bounds maximum'     => array(1,      3,      false,   3,     null,        true),
            'Greater than bounds maximum' => array(1,      3,      false,   4,     null,        false),

            'Boolean true'                => array(1,      null,   false,   true,  null,        true),
            'Boolean false'               => array(1,      null,   false,   false, null,        false),
            'Boolean true with never'     => array(null,   0,      false,   true,  null,        false),
            'Boolean false with never'    => array(null,   0,      false,   false, null,        true),

            'Always'                      => array(null,   3,      true,    2,     2,           true),
            'Not always'                  => array(null,   3,      true,    2,     3,           false),
        );
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

        $this->setExpectedException('Eloquent\Phony\Verification\Exception\InvalidSingularCardinalityException');
        $this->subject->assertSingular();
    }

    public function testAssertSingularFailureMaximum()
    {
        $this->subject = new Cardinality(null, 2);

        $this->setExpectedException('Eloquent\Phony\Verification\Exception\InvalidSingularCardinalityException');
        $this->subject->assertSingular();
    }
}
