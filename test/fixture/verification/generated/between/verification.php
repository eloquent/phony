<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$stub = Phony::stub(
    function ($animal) {
        yield strtoupper($animal);
    }
)->setLabel('label')->forwards();
$stub('aardvark');
$stub('bonobo');

// verification
$stub->between(3, 4)->generated();
