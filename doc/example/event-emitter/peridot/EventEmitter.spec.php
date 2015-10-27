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
            $newListener = x\spy();
            $this->emitter->on('newListener', $newListener);

            expect($this->emitter->on('eventA', $this->spyA))->to->be->equal($this->emitter);
            expect($this->emitter->on('eventB', $this->spyB))->to->be->equal($this->emitter);
            expect($this->emitter->on('eventA', $this->spyC))->to->be->equal($this->emitter);
            expect($this->emitter->on('eventB', $this->spyD))->to->be->equal($this->emitter);
            expect($this->emitter->on('eventA', $this->spyA))->to->be->equal($this->emitter);

            expect($this->emitter->listeners('eventA'))->to->be->equal(array($this->spyA, $this->spyC, $this->spyA));
            expect($this->emitter->listeners('eventB'))->to->be->equal(array($this->spyB, $this->spyD));

            $newListener->twice()->calledWith('eventA', $this->spyA);
            $newListener->calledWith('eventB', $this->spyB);
            $newListener->calledWith('eventA', $this->spyC);
            $newListener->calledWith('eventB', $this->spyD);
        });
    });

    describe('once()', function () {
        it('only calls the handler once', function () {
            $newListener = x\spy();
            $this->emitter->on('newListener', $newListener);

            expect($this->emitter->once('eventA', $this->spyA))->to->be->equal($this->emitter);

            $newListener->calledWith('eventA', any('Closure'));

            expect($this->emitter->emit('eventA', 1, 2))->to->be->true();
            expect($this->emitter->emit('eventA', 3, 4))->to->be->false();

            $this->spyA->once()->called();
            $this->spyA->calledWith(1, 2);
        });
    });

    describe('removeListener()', function () {
        it('removes listeners from the correct events', function () {
            $removeListener = x\spy();
            $this->emitter->on('removeListener', $removeListener);

            expect($this->emitter->removeListener('no-listeners', function () {}))->to->be->equal($this->emitter);

            $this->emitter->on('eventA', $this->spyA);
            $this->emitter->on('eventA', $this->spyB);
            $this->emitter->on('eventA', $this->spyA);
            $this->emitter->on('eventB', $this->spyA);

            expect($this->emitter->listeners('eventA'))->to->be->equal(array($this->spyA, $this->spyB, $this->spyA));
            expect($this->emitter->listeners('eventB'))->to->be->equal(array($this->spyA));

            expect($this->emitter->removeListener('eventA', $this->spyA))->to->be->equal($this->emitter);

            $removeListener->once()->calledWith('eventA', $this->spyA);
            expect($this->emitter->listeners('eventA'))->to->be->equal(array($this->spyA, $this->spyB));
            expect($this->emitter->listeners('eventB'))->to->be->equal(array($this->spyA));

            expect($this->emitter->removeListener('eventA', $this->spyA))->to->be->equal($this->emitter);

            $removeListener->twice()->calledWith('eventA', $this->spyA);
            expect($this->emitter->listeners('eventA'))->to->be->equal(array($this->spyB));
            expect($this->emitter->listeners('eventB'))->to->be->equal(array($this->spyA));

            expect($this->emitter->removeListener('eventA', $this->spyA))->to->be->equal($this->emitter);

            $removeListener->twice()->calledWith('eventA', $this->spyA);
            expect($this->emitter->listeners('eventA'))->to->be->equal(array($this->spyB));
            expect($this->emitter->listeners('eventB'))->to->be->equal(array($this->spyA));
        });
    });

    describe('removeAllListeners()', function () {
        it('removes all listeners from a specific event', function () {
            $removeListener = x\spy();
            $this->emitter->on('removeListener', $removeListener);

            $this->emitter->on('eventA', $this->spyA);
            $this->emitter->on('eventA', $this->spyB);
            $this->emitter->on('eventA', $this->spyA);
            $this->emitter->on('eventB', $this->spyA);

            expect($this->emitter->listeners('eventA'))->to->be->equal(array($this->spyA, $this->spyB, $this->spyA));
            expect($this->emitter->listeners('eventB'))->to->be->equal(array($this->spyA));

            expect($this->emitter->removeAllListeners('eventA'))->to->be->equal($this->emitter);

            expect($this->emitter->listeners('eventA'))->to->be->equal(array());
            expect($this->emitter->listeners('eventB'))->to->be->equal(array($this->spyA));
            x\inOrder(
                $removeListener->calledWith('eventA', $this->spyA),
                $removeListener->calledWith('eventA', $this->spyB),
                $removeListener->calledWith('eventA', $this->spyA)
            );
        });

        it('removes all listeners from all events', function () {
            $this->emitter->on('eventA', $this->spyA);
            $this->emitter->on('eventA', $this->spyB);
            $this->emitter->on('eventA', $this->spyA);
            $this->emitter->on('eventB', $this->spyA);

            expect($this->emitter->listeners('eventA'))->to->be->equal(array($this->spyA, $this->spyB, $this->spyA));
            expect($this->emitter->listeners('eventB'))->to->be->equal(array($this->spyA));

            expect($this->emitter->removeAllListeners())->to->be->equal($this->emitter);

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

    describe('listenerCount()', function () {
        it('returns the number of existing listeners', function () {
            expect($this->emitter->listenerCount('eventA'))->to->be->equal(0);

            $this->emitter->on('eventA', $this->spyA);
            $this->emitter->on('eventB', $this->spyB);
            $this->emitter->on('eventA', $this->spyC);
            $this->emitter->on('eventB', $this->spyD);
            $this->emitter->on('eventA', $this->spyA);

            expect($this->emitter->listenerCount('eventA'))->to->be->equal(3);
            expect($this->emitter->listenerCount('eventB'))->to->be->equal(2);
        });
    });

    describe('emit()', function () {
        it('emits events to the correct handlers', function () {
            expect($this->emitter->on('eventA', $this->spyA))->to->be->equal($this->emitter);
            expect($this->emitter->on('eventB', $this->spyB))->to->be->equal($this->emitter);
            expect($this->emitter->on('eventA', $this->spyC))->to->be->equal($this->emitter);
            expect($this->emitter->on('eventB', $this->spyD))->to->be->equal($this->emitter);
            expect($this->emitter->on('eventA', $this->spyA))->to->be->equal($this->emitter);

            expect($this->emitter->emit('eventA', 1, 2))->to->be->true();
            expect($this->emitter->emit('eventA', 3, 4))->to->be->true();
            expect($this->emitter->emit('no-listeners'))->to->be->false();

            x\inOrder(
                $this->spyA->calledWith(1, 2),
                $this->spyC->calledWith(1, 2),
                $this->spyA->calledWith(1, 2),
                $this->spyA->calledWith(3, 4),
                $this->spyC->calledWith(3, 4)
            );

            $this->spyB->never()->called();
            $this->spyD->never()->called();
        });
    });
});
