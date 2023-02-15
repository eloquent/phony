<?php

declare(strict_types=1);

namespace Eloquent\Phony\Verification;

use AllowDynamicProperties;
use Eloquent\Phony\Verification\Exception\InvalidCardinalityStateException;
use Eloquent\Phony\Verification\Exception\InvalidSingularCardinalityException;
use PHPUnit\Framework\TestCase;

#[AllowDynamicProperties]
class CardinalityTest extends TestCase
{
    protected function setUp(): void
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
        $this->assertSame(-1, $this->subject->maximum());
        $this->assertFalse($this->subject->isNever());
        $this->assertFalse($this->subject->isAlways());
    }

    public function testConstructorFailureNegativeMin()
    {
        $this->expectException(InvalidCardinalityStateException::class);
        new Cardinality(-1);
    }

    public function testConstructorFailureNegativeMax()
    {
        $this->expectException(InvalidCardinalityStateException::class);
        new Cardinality(0, -1);
    }

    public function testConstructorFailureInvalidMinMax()
    {
        $this->expectException(InvalidCardinalityStateException::class);
        new Cardinality($this->maximum, $this->minimum);
    }

    public function testConstructorFailureInvalidIsAlways()
    {
        $this->expectException(InvalidCardinalityStateException::class);
        new Cardinality(0, 0, true);
    }

    public function testConstructorFailureInvalidIsAny()
    {
        $this->expectException(InvalidCardinalityStateException::class);
        new Cardinality(0, -1);
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

        $this->expectException(InvalidCardinalityStateException::class);
        $this->subject->setIsAlways(true);
    }

    public function matchesData()
    {
        //                                    minimum maximum isAlways count  maximumCount expected
        return [
            'Less than minimum'           => [1,      -1,     false,   0,     1,           false],
            'Equal to minimum'            => [1,      -1,     false,   1,     1,           true],
            'Greater than minimum'        => [1,      -1,     false,   2,     1,           true],

            'Less than maximum'           => [0,      1,      false,   0,     1,           true],
            'Equal to maximum'            => [0,      1,      false,   1,     1,           true],
            'Greater than maximum'        => [0,      1,      false,   2,     1,           false],

            'Less than bounds minimum'    => [1,      3,      false,   0,     1,           false],
            'Equal to bounds minimum'     => [1,      3,      false,   1,     1,           true],
            'Within bounds'               => [1,      3,      false,   2,     1,           true],
            'Equal to bounds maximum'     => [1,      3,      false,   3,     1,           true],
            'Greater than bounds maximum' => [1,      3,      false,   4,     1,           false],

            'Boolean true'                => [1,      -1,     false,   true,  1,           true],
            'Boolean false'               => [1,      -1,     false,   false, 1,           false],
            'Boolean true with never'     => [0,      0,      false,   true,  1,           false],
            'Boolean false with never'    => [0,      0,      false,   false, 1,           true],

            'Always'                      => [0,      3,      true,    2,     2,           true],
            'Not always'                  => [0,      3,      true,    2,     3,           false],
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

        $this->expectException(InvalidSingularCardinalityException::class);
        $this->subject->assertSingular();
    }

    public function testAssertSingularFailureMaximum()
    {
        $this->subject = new Cardinality(0, 2);

        $this->expectException(InvalidSingularCardinalityException::class);
        $this->subject->assertSingular();
    }
}
