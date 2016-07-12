<?php

use Eloquent\Phony\Test\Phony;

// setup
$stub = Phony::stub()->setLabel('label')->setUseIterableSpies(true);
$stub->with('aardvark')->returns(array('AARDVARK'));
$stub->with('bonobo')->returns(array('BONOBO'));
$stub('aardvark');
$stub('bonobo');

// verification
$stub->between(3, 4)->iterated();
