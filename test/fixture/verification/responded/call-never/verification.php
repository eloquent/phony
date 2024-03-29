<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$stub = Phony::stub(
    function () {
        return 'aardvark';
    }
)->setLabel('label')->forwards();
$stub();
$stub();

// verification
$stub->lastCall()->never()->responded();
