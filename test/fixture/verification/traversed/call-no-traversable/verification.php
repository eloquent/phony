<?php

use Eloquent\Phony\Test\Phony;

// setup
$stub = Phony::stub()->setLabel('label');
$stub->with('aardvark')->returns('AARDVARK');
$stub->with('bonobo')->throws(new RuntimeException('BONOBO'));
$stub('aardvark');
try {
    $stub('bonobo');
} catch (RuntimeException $e) {
}

// verification
$stub->lastCall()->traversed();
