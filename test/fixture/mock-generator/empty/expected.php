<?php

class MockGeneratorEmpty
implements \Eloquent\Phony\Mock\MockInterface
{
    private static $_customMethods = array();
    private static $_staticProxy;
    private $_proxy;
}
