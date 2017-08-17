<?php

use Eloquent\Phony\Test\Phony;

// setup
$stub = Phony::stub()->setLabel('label')->setUseIterableSpies(true);
$stub->with('aardvark')->returns('AARDVARK');
$stub->with('bonobo')->throws(new RuntimeException('BONOBO'));
$stub->with('chameleon')->returns(['CHAMELEON']);
$stub->with('dugong')->returns(['DUGONG', 'MECHA-DUGONG']);
$stub->with('earwig')->returns(['EARWIG']);
$stub->with('ferret')->generates(['FERRET']);
$stub('aardvark');
try {
    $stub('bonobo');
} catch (RuntimeException $e) {
}
iterator_to_array($stub('chameleon'));
foreach ($stub('dugong') as $value) {
    break;
}
$stub('earwig');
$stub('ferret');

// verification
$stub->generated()->consumed();
