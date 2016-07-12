<?php

use Eloquent\Phony\Test\Phony;

// setup
$stub = Phony::stub()->setLabel('label')->setUseIterableSpies(true);
$stub->with('aardvark')->generates(array('AARDVARK'));
$stub->with('bonobo')->generates(array('BONOBO'));
iterator_to_array($stub('aardvark'));
iterator_to_array($stub('bonobo'));

// verification
$stub->lastCall()->generated()->produced('CHAMELEON');
