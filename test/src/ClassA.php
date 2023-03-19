<?php

class ClassA
{
    public static function staticMethodA(...$arguments)
    {
    }

    public static function __callStatic($name, array $arguments)
    {
    }

    public function methodA(...$arguments)
    {
    }

    public function __call($name, array $arguments)
    {
    }
}
