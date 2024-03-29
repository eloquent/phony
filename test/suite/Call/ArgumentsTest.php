<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call;

use AllowDynamicProperties;
use Eloquent\Phony\Call\Exception\UndefinedArgumentException;
use PHPUnit\Framework\TestCase;

#[AllowDynamicProperties]
class ArgumentsTest extends TestCase
{
    protected function setUp(): void
    {
        $this->a = 'a';
        $this->b = 'b';
        $this->arguments = [&$this->a, &$this->b];
        $this->subject = new Arguments($this->arguments);
    }

    public function testConstructor()
    {
        $this->assertSame($this->arguments, $this->subject->all());
        $this->assertSame($this->arguments, iterator_to_array($this->subject));
        $this->assertCount(2, $this->subject);
    }

    public function testCopy()
    {
        $copy = $this->subject->copy();

        $this->assertNotSame($this->subject, $copy);
        $this->assertEquals($this->subject, $copy);
        $this->assertSame($this->arguments, $copy->all());

        $copy->set('value');

        $this->assertSame('a', $this->subject->get());
        $this->assertSame('a', $this->arguments[0]);
    }

    public function testSet()
    {
        $this->assertSame($this->subject, $this->subject->set('c'));
        $this->assertSame($this->subject, $this->subject->set(1, 'd'));

        $this->assertSame(['c', 'd'], $this->subject->all());
        $this->assertSame('c', $this->a);
        $this->assertSame('d', $this->b);

        $this->assertSame($this->subject, $this->subject->set());

        $this->assertSame([null, 'd'], $this->subject->all());
        $this->assertNull($this->a);
    }

    public function testSetFailureTooHigh()
    {
        $this->expectException(UndefinedArgumentException::class);
        $this->subject->set(111, 'value');
    }

    public function testSetFailureTooLow()
    {
        $this->expectException(UndefinedArgumentException::class);
        $this->subject->set(-111, 'value');
    }

    public function testSetFailureNoArguments()
    {
        $this->subject = new Arguments([]);

        $this->expectException(UndefinedArgumentException::class);
        $this->subject->set('value');
    }

    public function testHas()
    {
        $this->assertTrue($this->subject->has());
        $this->assertTrue($this->subject->has(0));
        $this->assertTrue($this->subject->has(1));
        $this->assertTrue($this->subject->has(-1));
        $this->assertTrue($this->subject->has(-2));

        $this->assertFalse($this->subject->has(111));
        $this->assertFalse($this->subject->has(-111));

        $this->subject = new Arguments([]);

        $this->assertFalse($this->subject->has());
        $this->assertFalse($this->subject->has(0));
        $this->assertFalse($this->subject->has(1));
    }

    public function testGet()
    {
        $this->assertSame('a', $this->subject->get());
        $this->assertSame('a', $this->subject->get(0));
        $this->assertSame('b', $this->subject->get(1));
        $this->assertSame('b', $this->subject->get(-1));
        $this->assertSame('a', $this->subject->get(-2));
    }

    public function testGetFailureTooHigh()
    {
        $this->expectException(UndefinedArgumentException::class);
        $this->subject->get(111);
    }

    public function testGetFailureTooLow()
    {
        $this->expectException(UndefinedArgumentException::class);
        $this->subject->get(-111);
    }

    public function testGetFailureNoArguments()
    {
        $this->subject = new Arguments([]);

        $this->expectException(UndefinedArgumentException::class);
        $this->subject->get();
    }

    public function testCreate()
    {
        $this->assertEquals(new Arguments(['a', 'b']), Arguments::create('a', 'b'));
    }
}
