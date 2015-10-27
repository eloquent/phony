<?php

use Eloquent\Phony as x;

describe('EventEmitter', function () {
    beforeEach(function () {
        $this->emitter = new EventEmitter();

        $this->spyA = x\spy();
        $this->spyB = x\spy();
        $this->spyC = x\spy();
        $this->spyD = x\spy();
    });

    describe('on()', function () {
        it('adds listeners to the correct events', function () {
            $this->emitter->on('eventA', $this->spyA);
            $this->emitter->on('eventB', $this->spyB);
            $this->emitter->on('eventA', $this->spyC);
            $this->emitter->on('eventB', $this->spyD);
            $this->emitter->on('eventA', $this->spyA);

            expect($this->emitter->listeners('eventA'))->to->be->equal(array($this->spyA, $this->spyC, $this->spyA));
            expect($this->emitter->listeners('eventB'))->to->be->equal(array($this->spyB, $this->spyD));
        });
    });

    describe('once()', function () {
        it('removes the listener once it is called', function () {
            $this->emitter->once('eventA', $this->spyA);

            $this->emitter->emit('eventA', 1, 2);
            $this->emitter->emit('eventA', 3, 4);

            $this->spyA->once()->called();
            $this->spyA->calledWith(1, 2);
        });
    });

    describe('removeListener()', function () {
        it('removes listeners from the correct events', function () {
            $this->emitter->removeListener('no-listeners', function () {});

            $this->emitter->on('eventA', $this->spyA);
            $this->emitter->on('eventA', $this->spyB);
            $this->emitter->on('eventA', $this->spyA);
            $this->emitter->on('eventB', $this->spyA);

            expect($this->emitter->listeners('eventA'))->to->be->equal(array($this->spyA, $this->spyB, $this->spyA));
            expect($this->emitter->listeners('eventB'))->to->be->equal(array($this->spyA));

            $this->emitter->removeListener('eventA', $this->spyA);

            expect($this->emitter->listeners('eventA'))->to->be->equal(array($this->spyB, $this->spyA));
            expect($this->emitter->listeners('eventB'))->to->be->equal(array($this->spyA));

            $this->emitter->removeListener('eventA', $this->spyA);

            expect($this->emitter->listeners('eventA'))->to->be->equal(array($this->spyB));
            expect($this->emitter->listeners('eventB'))->to->be->equal(array($this->spyA));

            $this->emitter->removeListener('eventA', $this->spyA);

            expect($this->emitter->listeners('eventA'))->to->be->equal(array($this->spyB));
            expect($this->emitter->listeners('eventB'))->to->be->equal(array($this->spyA));
        });
    });

    describe('removeAllListeners()', function () {
        it('removes all listeners from a specific event', function () {
            $this->emitter->on('eventA', $this->spyA);
            $this->emitter->on('eventA', $this->spyB);
            $this->emitter->on('eventA', $this->spyA);
            $this->emitter->on('eventB', $this->spyA);

            expect($this->emitter->listeners('eventA'))->to->be->equal(array($this->spyA, $this->spyB, $this->spyA));
            expect($this->emitter->listeners('eventB'))->to->be->equal(array($this->spyA));

            $this->emitter->removeAllListeners('eventA');

            expect($this->emitter->listeners('eventA'))->to->be->equal(array());
            expect($this->emitter->listeners('eventB'))->to->be->equal(array($this->spyA));
        });

        it('removes all listeners from all events', function () {
            $this->emitter->on('eventA', $this->spyA);
            $this->emitter->on('eventA', $this->spyB);
            $this->emitter->on('eventA', $this->spyA);
            $this->emitter->on('eventB', $this->spyA);

            expect($this->emitter->listeners('eventA'))->to->be->equal(array($this->spyA, $this->spyB, $this->spyA));
            expect($this->emitter->listeners('eventB'))->to->be->equal(array($this->spyA));

            $this->emitter->removeAllListeners();

            expect($this->emitter->listeners('eventA'))->to->be->equal(array());
            expect($this->emitter->listeners('eventB'))->to->be->equal(array());
        });
    });

    describe('listeners()', function () {
        it('returns the existing listeners', function () {
            expect($this->emitter->listeners('eventA'))->to->be->equal(array());

            $this->emitter->on('eventA', $this->spyA);
            $this->emitter->on('eventB', $this->spyB);
            $this->emitter->on('eventA', $this->spyC);
            $this->emitter->on('eventB', $this->spyD);
            $this->emitter->on('eventA', $this->spyA);

            expect($this->emitter->listeners('eventA'))->to->be->equal(array($this->spyA, $this->spyC, $this->spyA));
            expect($this->emitter->listeners('eventB'))->to->be->equal(array($this->spyB, $this->spyD));
        });
    });

    describe('emit()', function () {
        it('emits events to the correct listeners', function () {
            $this->emitter->on('eventA', $this->spyA);
            $this->emitter->on('eventB', $this->spyB);
            $this->emitter->on('eventA', $this->spyC);
            $this->emitter->on('eventB', $this->spyD);
            $this->emitter->on('eventA', $this->spyA);

            $this->emitter->emit('eventA', 1, 2);
            $this->emitter->emit('eventA', 3, 4);
            $this->emitter->emit('no-listeners');

            $this->spyA->calledWith(1, 2);
            $this->spyC->calledWith(1, 2);
            $this->spyA->calledWith(1, 2);
            $this->spyA->calledWith(3, 4);
            $this->spyC->calledWith(3, 4);

            $this->spyB->never()->called();
            $this->spyD->never()->called();
        });
    });
});
