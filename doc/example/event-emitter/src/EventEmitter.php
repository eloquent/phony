<?php

class EventEmitter
{
    public function addListener($event, $listener)
    {
        if (!isset($this->listeners[$event])) {
            $this->listeners[$event] = array();
        }

        if (count($this->listeners[$event]) >= $this->maxListeners) {
            throw new OverflowException('Max listeners exceeded.');
        }

        $this->listeners[$event][] = $listener;
        $this->emit('newListener', $event, $listener);

        return $this;
    }

    public function on($event, $listener)
    {
        return $this->addListener($event, $listener);
    }

    public function once($event, $listener)
    {
        $emitter = $this;
        $onceListener = null;
        $onceListener = function () use (
            &$onceListener, // @codeCoverageIgnore
            $emitter,
            $event,
            $listener
        ) {
            $emitter->removeListener($event, $onceListener);

            call_user_func_array($listener, func_get_args());
        };

        return $this->addListener($event, $onceListener);
    }

    public function removeListener($event, $listener)
    {
        if (!isset($this->listeners[$event])) {
            return $this;
        }

        for ($i = count($this->listeners[$event]) - 1; $i >= 0; --$i) {
            if ($listener !== $this->listeners[$event][$i]) {
                continue;
            }

            $this->doRemoveListener($event, $i);

            break;
        }

        return $this;
    }

    public function removeAllListeners($event = null)
    {
        if (null === $event) {
            $events = array_keys($this->listeners);
        } else {
            $events = array($event);
        }

        foreach ($events as $event) {
            for ($i = count($this->listeners[$event]) - 1; $i >= 0; --$i) {
                $this->doRemoveListener($event, $i);
            }
        }

        return $this;
    }

    public function listeners($event)
    {
        if (isset($this->listeners[$event])) {
            return $this->listeners[$event];
        }

        return array();
    }

    public function listenerCount($event)
    {
        if (isset($this->listeners[$event])) {
            return count($this->listeners[$event]);
        }

        return 0;
    }

    public function emit($event)
    {
        if (!isset($this->listeners[$event])) {
            return false;
        }

        $arguments = func_get_args();
        $event = array_shift($arguments);

        for ($i = count($this->listeners[$event]) - 1; $i >= 0; --$i) {
            call_user_func_array($this->listeners[$event][$i], $arguments);
        }

        return true;
    }

    public function setMaxListeners($maxListeners)
    {
        $this->maxListeners = $maxListeners;
    }

    private function doRemoveListener($event, $index)
    {
        $listener = $this->listeners[$event][$index];

        if (count($this->listeners[$event]) < 2) {
            unset($this->listeners[$event]);
        } else {
            array_splice($this->listeners[$event], $index, 1);
        }

        $this->emit('removeListener', $event, $listener);

        return $this;
    }

    private $listeners = array();
    private $maxListeners = 10;
}
