<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$stub = Phony::stub(
    function ($animal) {
        return strtoupper($animal);
    }
)->setLabel('label')->forwards();
$stub('aardvark');
$stub('bonobo');

// verification
$stub->never()->returned('BONOBO');
