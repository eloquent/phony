<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$stub = Phony::stub()->setLabel('label')->setUseIterableSpies(true);
$stub->with('aardvark')->returns('AARDVARK');
$stub->with('bonobo')->throws(new RuntimeException('BONOBO'));
$stub->with('chameleon')->returns(['CHAMELEON']);
$stub->with('dugong')->generates(['DUGONG']);
$stub('aardvark');
try {
    $stub('bonobo');
} catch (RuntimeException $e) {
}
$stub('chameleon');
$stub('dugong');

// verification
$stub->iterated()->used();
