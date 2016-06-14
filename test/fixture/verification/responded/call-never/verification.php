<?php

use Eloquent\Phony\Test\Phony;

// setup
$stub = Phony::stub(
    function () {
        return 'aardvark';
    }
)->setLabel('label');
$stub();
$stub();

// verification
$stub->lastCall()->never()->responded();
