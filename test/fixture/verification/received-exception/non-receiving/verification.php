<?php

use Eloquent\Phony\Test\Phony;

// setup
$stub = Phony::stub()->setLabel('label')->setUseTraversableSpies(true);
$stub->with('aardvark')->returns(array('AARDVARK'));
$stub->with('bonobo')->generates(array('BONOBO'));
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