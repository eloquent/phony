<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$stub = Phony::stub()->setLabel('label')->setUseIterableSpies(true);
$stub->with('aardvark')->returns('AARDVARK');
$stub->with('bonobo')->throws(new RuntimeException('BONOBO'));
$stub->with('chameleon')->returns(['CHAMELEON']);
$stub->with('dugong')->returns(['DUGONG']);
$stub->with('earwig')->generates(['EARWIG']);
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
