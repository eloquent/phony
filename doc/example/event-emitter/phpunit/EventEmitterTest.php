<?php

use Eloquent\Phony\Phpunit\Phony;

class EventEmitterTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->emitter = new EventEmitter();

        $this->spyA = Phony::spy();
        $this->spyB = Phony::spy();
        $this->spyC = Phony::spy();
        $this->spyD = Phony::spy();
    }

    public function testOn()
    {
        $newListener = Phony::spy();
        $this->emitter->on('newListener', $newListener);

        $this->assertSame($this->emitter, $this->emitter->on('eventA', $this->spyA));
        $this->assertSame($this->emitter, $this->emitter->on('eventB', $this->spyB));
        $this->assertSame($this->emitter, $this->emitter->on('eventA', $this->spyC));
        $this->assertSame($this->emitter, $this->emitter->on('eventB', $this->spyD));
        $this->assertSame($this->emitter, $this->emitter->on('eventA', $this->spyA));

        $this->assertSame(array($this->spyA, $this->spyC, $this->spyA), $this->emitter->listeners('eventA'));
        $this->assertSame(array($this->spyB, $this->spyD), $this->emitter->listeners('eventB'));

        $newListener->twice()->calledWith('eventA', $this->spyA);
        $newListener->calledWith('eventB', $this->spyB);
        $newListener->calledWith('eventA', $this->spyC);
        $newListener->calledWith('eventB', $this->spyD);
    }

    public function testOnce()
    {
        $newListener = Phony::spy();
        $this->emitter->on('newListener', $newListener);

        $this->assertSame($this->emitter, $this->emitter->once('eventA', $this->spyA));

        $newListener->calledWith('eventA', $this->isInstanceOf('Closure'));

        $this->assertTrue($this->emitter->emit('eventA', 1, 2));
        $this->assertFalse($this->emitter->emit('eventA', 3, 4));

        $this->spyA->once()->called();
        $this->spyA->calledWith(1, 2);
    }

    public function testRemoveListener()
    {
        $removeListener = Phony::spy();
        $this->emitter->on('removeListener', $removeListener);

        $this->assertSame($this->emitter, $this->emitter->removeListener('no-listeners', function () {}));

        $this->emitter->on('eventA', $this->spyA);
        $this->emitter->on('eventA', $this->spyB);
        $this->emitter->on('eventA', $this->spyA);
        $this->emitter->on('eventB', $this->spyA);

        $this->assertSame(array($this->spyA, $this->spyB, $this->spyA), $this->emitter->listeners('eventA'));
        $this->assertSame(array($this->spyA), $this->emitter->listeners('eventB'));

        $this->assertSame($this->emitter, $this->emitter->removeListener('eventA', $this->spyA));

        $removeListener->once()->calledWith('eventA', $this->spyA);
        $this->assertSame(array($this->spyA, $this->spyB), $this->emitter->listeners('eventA'));
        $this->assertSame(array($this->spyA), $this->emitter->listeners('eventB'));

        $this->assertSame($this->emitter, $this->emitter->removeListener('eventA', $this->spyA));

        $removeListener->twice()->calledWith('eventA', $this->spyA);
        $this->assertSame(array($this->spyB), $this->emitter->listeners('eventA'));
        $this->assertSame(array($this->spyA), $this->emitter->listeners('eventB'));

        $this->assertSame($this->emitter, $this->emitter->removeListener('eventA', $this->spyA));

        $removeListener->twice()->calledWith('eventA', $this->spyA);
        $this->assertSame(array($this->spyB), $this->emitter->listeners('eventA'));
        $this->assertSame(array($this->spyA), $this->emitter->listeners('eventB'));
    }

    public function testRemoveAllListenersWithEvent()
    {
        $removeListener = Phony::spy();
        $this->emitter->on('removeListener', $removeListener);

        $this->emitter->on('eventA', $this->spyA);
        $this->emitter->on('eventA', $this->spyB);
        $this->emitter->on('eventA', $this->spyA);
        $this->emitter->on('eventB', $this->spyA);

        $this->assertSame(array($this->spyA, $this->spyB, $this->spyA), $this->emitter->listeners('eventA'));
        $this->assertSame(array($this->spyA), $this->emitter->listeners('eventB'));

        $this->assertSame($this->emitter, $this->emitter->removeAllListeners('eventA'));

        $this->assertSame(array(), $this->emitter->listeners('eventA'));
        $this->assertSame(array($this->spyA), $this->emitter->listeners('eventB'));
        Phony::inOrder(
            $removeListener->calledWith('eventA', $this->spyA),
            $removeListener->calledWith('eventA', $this->spyB),
            $removeListener->calledWith('eventA', $this->spyA)
        );
    }

    public function testRemoveAllListenersWithoutEvent()
    {
        $this->emitter->on('eventA', $this->spyA);
        $this->emitter->on('eventA', $this->spyB);
        $this->emitter->on('eventA', $this->spyA);
        $this->emitter->on('eventB', $this->spyA);

        $this->assertSame(array($this->spyA, $this->spyB, $this->spyA), $this->emitter->listeners('eventA'));
        $this->assertSame(array($this->spyA), $this->emitter->listeners('eventB'));

        $this->assertSame($this->emitter, $this->emitter->removeAllListeners());

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

    public function testListenerCount()
    {
        $this->assertSame(0, $this->emitter->listenerCount('eventA'));

        $this->emitter->on('eventA', $this->spyA);
        $this->emitter->on('eventB', $this->spyB);
        $this->emitter->on('eventA', $this->spyC);
        $this->emitter->on('eventB', $this->spyD);
        $this->emitter->on('eventA', $this->spyA);

        $this->assertSame(3, $this->emitter->listenerCount('eventA'));
        $this->assertSame(2, $this->emitter->listenerCount('eventB'));
    }

    public function testEmit()
    {
        $this->assertSame($this->emitter, $this->emitter->on('eventA', $this->spyA));
        $this->assertSame($this->emitter, $this->emitter->on('eventB', $this->spyB));
        $this->assertSame($this->emitter, $this->emitter->on('eventA', $this->spyC));
        $this->assertSame($this->emitter, $this->emitter->on('eventB', $this->spyD));
        $this->assertSame($this->emitter, $this->emitter->on('eventA', $this->spyA));

        $this->assertTrue($this->emitter->emit('eventA', 1, 2));
        $this->assertTrue($this->emitter->emit('eventA', 3, 4));
        $this->assertFalse($this->emitter->emit('no-listeners'));

        Phony::inOrder(
            $this->spyA->calledWith(1, 2),
            $this->spyC->calledWith(1, 2),
            $this->spyA->calledWith(1, 2),
            $this->spyA->calledWith(3, 4),
            $this->spyC->calledWith(3, 4)
        );

        $this->spyB->never()->called();
        $this->spyD->never()->called();
    }
}
