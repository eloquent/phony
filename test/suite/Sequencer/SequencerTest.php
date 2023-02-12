<?php

declare(strict_types=1);

namespace Eloquent\Phony\Sequencer;

use Eloquent\Phony\Test\WithDynamicProperties;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class SequencerTest extends TestCase
{
    use WithDynamicProperties;

    protected function setUp(): void
    {
        $this->subject = new Sequencer();
    }

    public function testConstructor()
    {
        $this->assertSame(-1, $this->subject->get());
    }

    public function testSet()
    {
        $this->subject->set(111);

        $this->assertSame(111, $this->subject->get());
    }

    public function testReset()
    {
        $this->subject->set(111);
        $this->subject->reset();

        $this->assertSame(-1, $this->subject->get());
    }

    public function testNext()
    {
        $this->assertSame(0, $this->subject->next());
        $this->assertSame(1, $this->subject->next());
        $this->assertSame(2, $this->subject->next());
    }

    public function testSequence()
    {
        $reflector = new ReflectionClass(Sequencer::class);
        $property = $reflector->getProperty('instances');
        $property->setAccessible(true);
        $instances = $property->getValue(null);
        $property->setValue(null, null);
        $instanceA = Sequencer::sequence('a');
        $instanceB = Sequencer::sequence('b');

        $this->assertInstanceOf(Sequencer::class, $instanceA);
        $this->assertInstanceOf(Sequencer::class, $instanceB);
        $this->assertSame($instanceA, Sequencer::sequence('a'));
        $this->assertSame($instanceB, Sequencer::sequence('b'));
        $this->assertNotSame($instanceA, $instanceB);

        $property->setValue(null, $instances);
    }
}
