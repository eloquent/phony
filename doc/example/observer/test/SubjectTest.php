<?php

use Eloquent\Phony\Phpunit\Phony;

class SubjectTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->subject = new Subject();

        $this->observerA = Phony::mock('SplObserver');
        $this->observerB = Phony::mock('SplObserver');
    }

    public function testAttachAndNotify()
    {
        $this->subject->attach($this->observerA->mock());
        $this->subject->attach($this->observerB->mock());
        $this->subject->notify();

        $this->observerA->update->calledWith($this->subject);
        $this->observerB->update->calledWith($this->subject);
    }

    public function testDetachAndNotify()
    {
        $this->subject->attach($this->observerA->mock());
        $this->subject->attach($this->observerB->mock());
        $this->subject->detach($this->observerA->mock());
        $this->subject->notify();

        $this->observerA->update->never()->called();
        $this->observerB->update->calledWith($this->subject);
    }
}
