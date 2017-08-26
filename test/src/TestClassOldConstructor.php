<?php

class TestClassOldConstructor
{
    public function TestClassOldConstructor()
    {
        $this->constructorArguments = func_get_args();
    }

    public $constructorArguments;
}
