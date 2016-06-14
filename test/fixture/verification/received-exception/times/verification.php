<?php

use Eloquent\Phony\Test\Phony;

// setup
$stub = Phony::stub()->setLabel('label')->setUseTraversableSpies(true);
$stub->with('aardvark')->returns(array('AARDVARK'));
$stub->with('bonobo')->generates(array('BONOBO', 'BADGER'));
$stub->with('chameleon')->generates(array('CHAMELEON'));
iterator_to_array($stub('aardvark'));
$generator = $stub('bonobo');
try {
    $generator->throw(new RuntimeException('MECHA-BONOBO'));
} catch (RuntimeException $e) {
}
iterator_to_array($stub('chameleon'));

// verification
$stub->generated()->times(2)->receivedException(new RuntimeException('MECHA-BONOBO'));
