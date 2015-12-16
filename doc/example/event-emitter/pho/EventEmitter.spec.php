<?php

use Eloquent\Phony\Pho as x;

pho\describe('EventEmitter', function () {
    pho\beforeEach(function () {
        $this->emitter = new EventEmitter();

        $this->spyA = x\spy();
        $this->spyB = x\spy();
        $this->spyC = x\spy();
        $this->spyD = x\spy();
    });

    pho\describe('on()', function () {
        pho\it('adds listeners to the correct events', function () {
            $this->emitter->on('eventA', $this->spyA);
            $this->emitter->on('eventB', $this->spyB);
            $this->emitter->on('eventA', $this->spyC);
            $this->emitter->on('eventB', $this->spyD);
            $this->emitter->on('eventA', $this->spyA);

            pho\expect($this->emitter->listeners('eventA'))->toBe(array($this->spyA, $this->spyC, $this->spyA));
            pho\expect($this->emitter->listeners('eventB'))->toBe(array($this->spyB, $this->spyD));
        });
    });

    pho\describe('once()', function () {
        pho\it('removes the listener once it is called', function () {
            $this->emitter->once('eventA', $this->spyA);

            $this->emitter->emit('eventA', array(1, 2));
            $this->emitter->emit('eventA', array(3, 4));

            $this->spyA->once()->called();
            $this->spyA->calledWith(1, 2);
        });
    });

    pho\describe('removeListener()', function () {
        pho\it('removes listeners from the correct events', function () {
            $this->emitter->removeListener('no-listeners', function () {});

            $this->emitter->on('eventA', $this->spyA);
            $this->emitter->on('eventA', $this->spyB);
            $this->emitter->on('eventA', $this->spyA);
            $this->emitter->on('eventB', $this->spyA);

            pho\expect($this->emitter->listeners('eventA'))->toBe(array($this->spyA, $this->spyB, $this->spyA));
            pho\expect($this->emitter->listeners('eventB'))->toBe(array($this->spyA));

            $this->emitter->removeListener('eventA', $this->spyA);

            pho\expect($this->emitter->listeners('eventA'))->toBe(array($this->spyB, $this->spyA));
            pho\expect($this->emitter->listeners('eventB'))->toBe(array($this->spyA));

            $this->emitter->removeListener('eventA', $this->spyA);

            pho\expect($this->emitter->listeners('eventA'))->toBe(array($this->spyB));
            pho\expect($this->emitter->listeners('eventB'))->toBe(array($this->spyA));

            $this->emitter->removeListener('eventA', $this->spyA);

            pho\expect($this->emitter->listeners('eventA'))->toBe(array($this->spyB));
            pho\expect($this->emitter->listeners('eventB'))->toBe(array($this->spyA));
        });
    });

    pho\describe('removeAllListeners()', function () {
        pho\it('removes all listeners from a specific event', function () {
            $this->emitter->on('eventA', $this->spyA);
            $this->emitter->on('eventA', $this->spyB);
            $this->emitter->on('eventA', $this->spyA);
            $this->emitter->on('eventB', $this->spyA);

            pho\expect($this->emitter->listeners('eventA'))->toBe(array($this->spyA, $this->spyB, $this->spyA));
            pho\expect($this->emitter->listeners('eventB'))->toBe(array($this->spyA));

            $this->emitter->removeAllListeners('eventA');

            pho\expect($this->emitter->listeners('eventA'))->toBe(array());
            pho\expect($this->emitter->listeners('eventB'))->toBe(array($this->spyA));
        });

        pho\it('removes all listeners from all events', function () {
            $this->emitter->on('eventA', $this->spyA);
            $this->emitter->on('eventA', $this->spyB);
            $this->emitter->on('eventA', $this->spyA);
            $this->emitter->on('eventB', $this->spyA);

            pho\expect($this->emitter->listeners('eventA'))->toBe(array($this->spyA, $this->spyB, $this->spyA));
            pho\expect($this->emitter->listeners('eventB'))->toBe(array($this->spyA));

            $this->emitter->removeAllListeners();

            pho\expect($this->emitter->listeners('eventA'))->toBe(array());
            pho\expect($this->emitter->listeners('eventB'))->toBe(array());
        });
    });

    pho\describe('listeners()', function () {
        pho\it('returns the existing listeners', function () {
            pho\expect($this->emitter->listeners('eventA'))->toBe(array());

            $this->emitter->on('eventA', $this->spyA);
            $this->emitter->on('eventB', $this->spyB);
            $this->emitter->on('eventA', $this->spyC);
            $this->emitter->on('eventB', $this->spyD);
            $this->emitter->on('eventA', $this->spyA);

            pho\expect($this->emitter->listeners('eventA'))->toBe(array($this->spyA, $this->spyC, $this->spyA));
            pho\expect($this->emitter->listeners('eventB'))->toBe(array($this->spyB, $this->spyD));
        });
    });

    pho\describe('emit()', function () {
        pho\it('emits events to the correct listeners', function () {
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
        });
    });
});
