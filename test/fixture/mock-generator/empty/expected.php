<?php

class MockGeneratorEmpty
implements \Eloquent\Phony\Mock\Mock
{
    private static $_uncallableMethods = [];
    private static $_traitMethods = [];
    private static $_customMethods = [];
    private static $_staticHandle;
    private $_handle;
}
