<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$stub = Phony::stub()->setLabel('label')->setUseIterableSpies(true);
$stub->with('aardvark')->returns(['AARDVARK']);
$stub->with('bonobo')->generates(['BONOBO', 'BADGER']);
iterator_to_array($stub('aardvark'));
$generator = $stub('bonobo');
try {
    $generator->throw(new RuntimeException('MECHA-BONOBO'));
} catch (RuntimeException $e) {
}

// verification
$stub->lastCall()->generated()->never()->receivedException(RuntimeException::class);
