<?php

class ClassA
{
    public static function staticMethodA()
    {
    }

    public static function __callStatic($name, array $arguments)
    {
    }

    public function methodA()
    {
    }

    public function __call($name, array $arguments)
    {
    }
}
