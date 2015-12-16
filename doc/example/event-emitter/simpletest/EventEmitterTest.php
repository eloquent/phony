<?php

use Eloquent\Phony\Simpletest\Phony;

class EventEmitterTest extends UnitTestCase
{
    public function setUp()
    {
        $this->emitter = new EventEmitter();

        $this->spyA = Phony::spy();
        $this->spyB = Phony::spy();
        $this->spyC = Phony::spy();
        $this->spyD = Phony::spy();
    }

    public function testOn()
    {
        $this->emitter->on('eventA', $this->spyA);
        $this->emitter->on('eventB', $this->spyB);
        $this->emitter->on('eventA', $this->spyC);
        $this->emitter->on('eventB', $this->spyD);
        $this->emitter->on('eventA', $this->spyA);

        $this->assertSame(array($this->spyA, $this->spyC, $this->spyA), $this->emitter->listeners('eventA'));
        $this->assertSame(array($this->spyB, $this->spyD), $this->emitter->listeners('eventB'));
    }

    public function testOnce()
    {
        $this->emitter->once('eventA', $this->spyA);

        $this->emitter->emit('eventA', array(1, 2));
        $this->emitter->emit('eventA', array(3, 4));

        $this->spyA->once()->called();
        $this->spyA->calledWith(1, 2);
    }

    public function testRemoveListener()
    {
        $this->emitter->removeListener('no-listeners', function () {});

        $this->emitter->on('eventA', $this->spyA);
        $this->emitter->on('eventA', $this->spyB);
        $this->emitter->on('eventA', $this->spyA);
        $this->emitter->on('eventB', $this->spyA);

        $this->assertSame(array($this->spyA, $this->spyB, $this->spyA), $this->emitter->listeners('eventA'));
        $this->assertSame(array($this->spyA), $this->emitter->listeners('eventB'));

        $this->emitter->removeListener('eventA', $this->spyA);

        $this->assertSame(array($this->spyB, $this->spyA), $this->emitter->listeners('eventA'));
        $this->assertSame(array($this->spyA), $this->emitter->listeners('eventB'));

        $this->emitter->removeListener('eventA', $this->spyA);

        $this->assertSame(array($this->spyB), $this->emitter->listeners('eventA'));
        $this->assertSame(array($this->spyA), $this->emitter->listeners('eventB'));

        $this->emitter->removeListener('eventA', $this->spyA);

        $this->assertSame(array($this->spyB), $this->emitter->listeners('eventA'));
        $this->assertSame(array($this->spyA), $this->emitter->listeners('eventB'));
    }

    public function testRemoveAllListenersWithEvent()
    {
        $this->emitter->on('eventA', $this->spyA);
        $this->emitter->on('eventA', $this->spyB);
        $this->emitter->on('eventA', $this->spyA);
        $this->emitter->on('eventB', $this->spyA);

        $this->assertSame(array($this->spyA, $this->spyB, $this->spyA), $this->emitter->listeners('eventA'));
        $this->assertSame(array($this->spyA), $this->emitter->listeners('eventB'));

        $this->emitter->removeAllListeners('eventA');

        $this->assertSame(array(), $this->emitter->listeners('eventA'));
        $this->assertSame(array($this->spyA), $this->emitter->listeners('eventB'));
    }

    public function testRemoveAllListenersWithoutEvent()
    {
        $this->emitter->on('eventA', $this->spyA);
        $this->emitter->on('eventA', $this->spyB);
        $this->emitter->on('eventA', $this->spyA);
        $this->emitter->on('eventB', $this->spyA);

        $this->assertSame(array($this->spyA, $this->spyB, $this->spyA), $this->emitter->listeners('eventA'));
        $this->assertSame(array($this->spyA), $this->emitter->listeners('eventB'));

        $this->emitter->removeAllListeners();

        $this->assertSame(array(), $this->emitter->listeners('eventA'));
        $this->assertSame(array(), $this->emitter->listeners('eventB'));
    }

    public function testListeners()
    {
        $this->assertSame(array(), $this->emitter->listeners('eventA'));

        $this->emitter->on('eventA', $this->spyA);
        $this->emitter->on('eventB', $this->spyB);
        $this->emitter->on('eventA', $this->spyC);
        $this->emitter->on('eventB', $this->spyD);
        $this->emitter->on('eventA', $this->spyA);

        $this->assertSame(array($this->spyA, $this->spyC, $this->spyA), $this->emitter->listeners('eventA'));
        $this->assertSame(array($this->spyB, $this->spyD), $this->emitter->listeners('eventB'));
    }

    public function testEmit()
    {
        $this->emitter->on('eventA', $this->spyA);
        $this->emitter->on('eventB', $this->spyB);
        $this->emitter->on('eventA', $this->spyC);
        $this->emitter->on('eventB', $this->spyD);
        $this->emitter->on('eventA', $this->spyA);

        $this->emitter->emit('eventA', array(1, 2));
        $this->emitter->emit('eventA', array(3, 4));
        $this->emitter->emit('eventA');
        $this->emitter->emit('no-listeners');

        $this->spyA->calledWith(1, 2);
        $this->spyC->calledWith(1, 2);
        $this->spyA->calledWith(3, 4);
        $this->spyC->calledWith(3, 4);
        $this->spyA->calledWith();
        $this->spyC->calledWith();

        $this->spyB->never()->called();
        $this->spyD->never()->called();
    }
}
