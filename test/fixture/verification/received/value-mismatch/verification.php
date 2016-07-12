<?php

use Eloquent\Phony\Test\Phony;

// setup
$stub = Phony::stub()->setLabel('label')->setUseIterableSpies(true);
$stub->with('aardvark')->returns(array('AARDVARK'));
$stub->with('bonobo')->generates(array('BONOBO', 'BADGER'));
$stub->with('chameleon')->returns('CHAMELEON');
$stub->with('dugong')->throws(new RuntimeException('DUGONG'));
iterator_to_array($stub('aardvark'));
$generator = $stub('bonobo');
$generator->send('MECHA-BONOBO');
$generator->send('MECHA-BADGER');
$stub('chameleon');
try {
    $stub('dugong');
} catch (RuntimeException $e) {
}

// verification
$stub->generated()->received('MECHA-CHAMELEON');
