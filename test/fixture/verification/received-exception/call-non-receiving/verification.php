<?php

use Eloquent\Phony\Test\Phony;

// setup
$stub = Phony::stub()->setLabel('label')->setUseIterableSpies(true);
$stub->with('aardvark')->returns(array('AARDVARK'));
$stub->with('bonobo')->generates(array('BONOBO'));
iterator_to_array($stub('aardvark'));
$generator = $stub('bonobo');
$generator->send('MECHA-BONOBO');

// verification
$stub->lastCall()->generated()->receivedException();
