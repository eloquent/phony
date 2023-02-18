<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$stub = null;
$stub = Phony::stub(
    function ($verify) use (&$stub) {
        $verify();
    }
)->setLabel('label')->forwards();

// verification
$stub(
    function () use ($stub) {
        $stub->lastCall()->responded();
    }
);
