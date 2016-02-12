<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Argument;

use Eloquent\Phony\Collection\IndexNormalizer;
use PHPUnit_Framework_TestCase;

class ArgumentsTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->a = 'a';
        $this->b = 'b';
        $this->arguments = array(&$this->a, &$this->b);
        $this->indexNormalizer = new IndexNormalizer();
        $this->subject = new Arguments($this->arguments, $this->indexNormalizer);
    }

    public function testConstructor()
    {
        $this->assertSame($this->arguments, $this->subject->all());
        $this->assertSame($this->arguments, iterator_to_array($this->subject));
        $this->assertSame(2, count($this->subject));
        $this->assertSame($this->indexNormalizer, $this->subject->indexNormalizer());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new Arguments();

        $this->assertSame(array(), $this->subject->all());
        $this->assertSame(IndexNormalizer::instance(), $this->subject->indexNormalizer());
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

        $this->assertSame(array('c', 'd'), $this->subject->all());
        $this->assertSame('c', $this->a);
        $this->assertSame('d', $this->b);

        $this->assertSame($this->subject, $this->subject->set());

        $this->assertSame(array(null, 'd'), $this->subject->all());
        $this->assertNull($this->a);
    }

    public function testSetFailureTooHigh()
    {
        $this->setExpectedException('Eloquent\Phony\Call\Argument\Exception\UndefinedArgumentException');
        $this->subject->set(111, 'value');
    }

    public function testSetFailureTooLow()
    {
        $this->setExpectedException('Eloquent\Phony\Call\Argument\Exception\UndefinedArgumentException');
        $this->subject->set(-111, 'value');
    }

    public function testSetFailureNoArguments()
    {
        $this->subject = new Arguments();

        $this->setExpectedException('Eloquent\Phony\Call\Argument\Exception\UndefinedArgumentException');
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

        $this->subject = new Arguments();

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
        $this->setExpectedException('Eloquent\Phony\Call\Argument\Exception\UndefinedArgumentException');
        $this->subject->get(111);
    }

    public function testGetFailureTooLow()
    {
        $this->setExpectedException('Eloquent\Phony\Call\Argument\Exception\UndefinedArgumentException');
        $this->subject->get(-111);
    }

    public function testGetFailureNoArguments()
    {
        $this->subject = new Arguments();

        $this->setExpectedException('Eloquent\Phony\Call\Argument\Exception\UndefinedArgumentException');
        $this->subject->get();
    }

    public function testAdapt()
    {
        $this->assertSame($this->subject, Arguments::adapt($this->subject));
        $this->assertNotSame($this->subject, Arguments::adapt($this->arguments));
        $this->assertEquals($this->subject, Arguments::adapt($this->arguments));
    }
}
