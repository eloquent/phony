<?php

use Eloquent\Phony\Test\Phony;

// setup
$stub = Phony::stub()->setLabel('label')->setUseIterableSpies(true);
$stub->with('aardvark')->returns(array('AARDVARK'));
$stub->with('bonobo')->generates(array('BONOBO'));
iterator_to_array($stub('aardvark'));
$generator = $stub('bonobo');
try {
    $generator->throw(new RuntimeException('MECHA-BONOBO'));
} catch (RuntimeException $e) {
}

// verification
$stub->lastCall()->generated()->receivedException(new RuntimeException('MECHA-CHAMELEON'));
