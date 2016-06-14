<?php

use Eloquent\Phony\Test\Phony;

// setup
$stub = Phony::stub(
    function ($animal) {
        return strtoupper($animal);
    }
)->setLabel('label');
$stub('aardvark');
$stub('bonobo');

// verification
$stub->times(3)->returned();
