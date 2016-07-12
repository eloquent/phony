<?php

use Eloquent\Phony\Test\Phony;

// setup
$stub = Phony::stub()->setLabel('label')->setUseIterableSpies(true);
$stub->with('aardvark')->returns(array('AARDVARK'));
$stub->with('bonobo')->generates(array('BONOBO', 'BADGER'));
iterator_to_array($stub('aardvark'));
$generator = $stub('bonobo');
$generator->send('MECHA-BONOBO');
$generator->send('MECHA-BADGER');

// verification
$stub->lastCall()->generated()->times(3)->received();
