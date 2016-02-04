<?php

class MockGeneratorEmpty
implements \Eloquent\Phony\Mock\MockInterface
{
    private static $_uncallableMethods = array();
    private static $_traitMethods = array();
    private static $_customMethods = array();
    private static $_staticHandle;
    private $_handle;
}
