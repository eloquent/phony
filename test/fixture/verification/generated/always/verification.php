<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$stub = Phony::stub()->setLabel('label');
$stub->with('aardvark')->returns('AARDVARK');
$stub->with('bonobo')->generates()->yields('BONOBO');
$stub('aardvark');
$stub('bonobo');

// verification
$stub->always()->generated();
