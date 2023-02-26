<?php

class MockGeneratorEmpty
implements \Eloquent\Phony\Mock\Mock
{
    private static $_staticHandle;
    private readonly \Eloquent\Phony\Mock\Handle\InstanceHandle $_handle;
}
