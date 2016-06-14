<?php

use Eloquent\Phony\Test\Phony;

// setup
$stub = Phony::stub()->setLabel('label')->setUseTraversableSpies(true);
$stub->with('aardvark')->returns('AARDVARK');
$stub->with('bonobo')->throws(new RuntimeException('BONOBO'));
$stub->with('chameleon')->returns(array('CHAMELEON'));
$stub->with('dugong')->returns(array('DUGONG'));
$stub->with('earwig')->generates(array('EARWIG'));
$stub('aardvark');
try {
    $stub('bonobo');
} catch (RuntimeException $e) {
}
iterator_to_array($stub('chameleon'));
$stub('dugong');
$stub('earwig');

// verification
$stub->generated()->used();
