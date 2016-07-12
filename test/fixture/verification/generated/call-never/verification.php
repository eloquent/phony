<?php

use Eloquent\Phony\Test\Phony;

// setup
$stub = Phony::stub(
    function ($animal) {
        yield strtoupper($animal);
    }
)->setLabel('label')->forwards();
$stub('aardvark');
$stub('bonobo');

// verification
$stub->lastCall()->never()->generated();
