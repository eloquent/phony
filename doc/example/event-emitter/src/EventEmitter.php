<?php

class EventEmitter
{
    public function on($event, $listener)
    {
        if (isset($this->listeners[$event])) {
            $this->listeners[$event][] = $listener;
        } else {
            $this->listeners[$event] = array($listener);
        }
    }

    public function once($event, $listener)
    {
        $emitter = $this;
        $once = function () use (&$once, $emitter, $event, $listener) {
            $emitter->removeListener($event, $once);

            call_user_func_array($listener, func_get_args());
        };

        $this->on($event, $once);
    }

    public function removeListener($event, $listener)
    {
        if (isset($this->listeners[$event])) {
            $index = array_search($listener, $this->listeners[$event], true);

            if (false !== $index) {
                array_splice($this->listeners[$event], $index, 1);
            }
        }
    }

    public function removeAllListeners($event = null)
    {
        if (null === $event) {
            $this->listeners = array();
        } else {
            unset($this->listeners[$event]);
        }
    }

    public function listeners($event)
    {
        if (isset($this->listeners[$event])) {
            return $this->listeners[$event];
        }

        return array();
    }

    public function emit($event, array $arguments = array())
    {
        if (isset($this->listeners[$event])) {
            foreach ($this->listeners[$event] as $listener) {
                call_user_func_array($listener, $arguments);
            }
        }
    }

    private $listeners = array();
}
