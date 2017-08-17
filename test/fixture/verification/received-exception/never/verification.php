<?php

use Eloquent\Phony\Test\Phony;

// setup
$stub = Phony::stub()->setLabel('label')->setUseIterableSpies(true);
$stub->with('aardvark')->returns(['AARDVARK']);
$stub->with('bonobo')->generates(['BONOBO', 'BADGER']);
$stub->with('chameleon')->generates(['CHAMELEON']);
$stub->with('dugong')->returns(['DUGONG']);
iterator_to_array($stub('aardvark'));
$generator = $stub('bonobo');
try {
    $generator->throw(new RuntimeException('MECHA-BONOBO'));
} catch (RuntimeException $e) {
}
$stub('chameleon');
$stub('dugong');

// verification
$stub->generated()->never()->receivedException();
