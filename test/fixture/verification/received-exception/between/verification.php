<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$stub = Phony::stub()->setLabel('label')->setUseIterableSpies(true);
$stub->with('aardvark')->returns(['AARDVARK']);
$stub->with('bonobo')->generates(['BONOBO', 'BADGER']);
$stub->with('chameleon')->generates(['CHAMELEON']);
iterator_to_array($stub('aardvark'));
$generator = $stub('bonobo');
try {
    $generator->throw(new RuntimeException('MECHA-BONOBO'));
} catch (RuntimeException $e) {
}
iterator_to_array($stub('chameleon'));

// verification
$stub->generated()->between(2, 3)->receivedException(new RuntimeException('MECHA-BONOBO'));
