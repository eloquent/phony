<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$stub = Phony::stub()->setLabel('label')->setUseIterableSpies(true);
$stub->with('aardvark')->generates()->throws(new RuntimeException('AARDVARK'));
$stub->with('bonobo')->generates()->throws(new RuntimeException('BONOBO'));
try {
    iterator_to_array($stub('aardvark'));
} catch (RuntimeException $e) {
}
try {
    iterator_to_array($stub('bonobo'));
} catch (RuntimeException $e) {
}

// verification
$stub->generated()->between(3, 4)->threw();
