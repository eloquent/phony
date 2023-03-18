<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call;

use AllowDynamicProperties;
use Eloquent\Phony\Call\Exception\UndefinedNamedArgumentException;
use Eloquent\Phony\Call\Exception\UndefinedPositionalArgumentException;
use PHPUnit\Framework\TestCase;

#[AllowDynamicProperties]
class ArgumentsTest extends TestCase
{
    protected function setUp(): void
    {
        $this->a = 1;
        $this->b = 2;
        $this->c = 3;
        $this->arguments = [&$this->a, &$this->b, 'c' => &$this->c];
        $this->subject = new Arguments($this->arguments);
    }

    public function testConstructor()
    {
        $this->assertSame($this->arguments, $this->subject->all());
        $this->assertSame([&$this->a, &$this->b], $this->subject->positional());
        $this->assertSame(['c' => &$this->c], $this->subject->named());
        $this->assertSame($this->arguments, iterator_to_array($this->subject));
        $this->assertCount(3, $this->subject);
    }

    public function testCopy()
    {
        $copy = $this->subject->copy();

        $this->assertNotSame($this->subject, $copy);
        $this->assertEquals($this->subject, $copy);
        $this->assertSame($this->arguments, $copy->all());

        $copy->set('value');

        $this->assertSame(1, $this->subject->get());
        $this->assertSame(1, $this->arguments[0]);
    }

    public function testSet()
    {
        $this->assertSame($this->subject, $this->subject->set(4));
        $this->assertSame($this->subject, $this->subject->set(1, 5));
        $this->assertSame($this->subject, $this->subject->set('c', 6));

        $this->assertSame([4, 5, 'c' => 6], $this->subject->all());
        $this->assertSame(4, $this->a);
        $this->assertSame(5, $this->b);
        $this->assertSame(6, $this->c);

        $this->assertSame($this->subject, $this->subject->set());

        $this->assertSame([null, 5, 'c' => 6], $this->subject->all());
        $this->assertNull($this->a);

        $this->assertSame($this->subject, $this->subject->set(7));

        $this->assertSame([7, 5, 'c' => 6], $this->subject->all());
        $this->assertSame(7, $this->a);

        $this->assertSame($this->subject, $this->subject->set(-1, 8));
        $this->assertSame($this->subject, $this->subject->set(-2, 9));

        $this->assertSame([9, 8, 'c' => 6], $this->subject->all());
        $this->assertSame(9, $this->a);
        $this->assertSame(8, $this->b);
    }

    public function testSetFirstNamed()
    {
        $a = 1;
        $b = 2;
        $arguments = ['a' => &$a, 'b' => &$b];
        $subject = new Arguments($arguments);

        $this->assertSame($subject, $subject->set());

        $this->assertSame(['a' => null, 'b' => 2], $subject->all());
        $this->assertNull($a);

        $this->assertSame($subject, $subject->set(3));

        $this->assertSame(['a' => 3, 'b' => 2], $subject->all());
        $this->assertSame(3, $a);
    }

    public function testSetFailureTooHigh()
    {
        $this->expectException(UndefinedPositionalArgumentException::class);
        $this->subject->set(111, 222);
    }

    public function testSetFailureTooLow()
    {
        $this->expectException(UndefinedPositionalArgumentException::class);
        $this->subject->set(-111, 222);
    }

    public function testSetFailureNonexistentPositional()
    {
        $this->subject = new Arguments([]);

        $this->expectException(UndefinedPositionalArgumentException::class);
        $this->subject->set(0, null);
    }

    public function testSetFailureNonexistentNamed()
    {
        $this->subject = new Arguments([]);

        $this->expectException(UndefinedNamedArgumentException::class);
        $this->subject->set('x', null);
    }

    public function testHas()
    {
        $this->assertTrue($this->subject->has());
        $this->assertTrue($this->subject->has(0));
        $this->assertTrue($this->subject->has(1));
        $this->assertTrue($this->subject->has('c'));
        $this->assertTrue($this->subject->has(-1));
        $this->assertTrue($this->subject->has(-2));

        $this->assertFalse($this->subject->has(111));
        $this->assertFalse($this->subject->has(-111));

        $this->subject = new Arguments([]);

        $this->assertFalse($this->subject->has());
        $this->assertFalse($this->subject->has(0));
        $this->assertFalse($this->subject->has(1));
        $this->assertFalse($this->subject->has('x'));
    }

    public function testGet()
    {
        $this->assertSame(1, $this->subject->get());
        $this->assertSame(1, $this->subject->get(0));
        $this->assertSame(2, $this->subject->get(1));
        $this->assertSame(3, $this->subject->get('c'));
        $this->assertSame(2, $this->subject->get(-1));
        $this->assertSame(1, $this->subject->get(-2));
    }

    public function testGetFailureTooHigh()
    {
        $this->expectException(UndefinedPositionalArgumentException::class);
        $this->subject->get(111);
    }

    public function testGetFailureTooLow()
    {
        $this->expectException(UndefinedPositionalArgumentException::class);
        $this->subject->get(-111);
    }

    public function testGetFailureNonexistentPositional()
    {
        $this->subject = new Arguments([]);

        $this->expectException(UndefinedPositionalArgumentException::class);
        $this->subject->get(0);
    }

    public function testGetFailureNonexistentNamed()
    {
        $this->subject = new Arguments([]);

        $this->expectException(UndefinedNamedArgumentException::class);
        $this->subject->get('x');
    }

    public function testCreate()
    {
        $this->assertEquals(new Arguments([1, 2, 'c' => 3]), Arguments::create(1, 2, c: 3));
    }
}
