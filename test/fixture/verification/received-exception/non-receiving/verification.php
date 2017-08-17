<?php

use Eloquent\Phony\Test\Phony;

// setup
$stub = Phony::stub()->setLabel('label')->setUseIterableSpies(true);
$stub->with('aardvark')->returns(['AARDVARK']);
$stub->with('bonobo')->generates(['BONOBO']);
$stub->with('chameleon')->returns('CHAMELEON');
$stub->with('dugong')->throws(new RuntimeException('DUGONG'));
iterator_to_array($stub('aardvark'));
$generator = $stub('bonobo');
$generator->send('MECHA-BONOBO');
$stub('chameleon');
try {
    $stub('dugong');
} catch (RuntimeException $e) {
}

// verification
$stub->generated()->receivedException();
