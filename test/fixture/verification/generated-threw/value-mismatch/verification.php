<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$stub = Phony::stub()->setLabel('label')->setUseIterableSpies(true);
$stub->with('aardvark')->generates()->throws(new RuntimeException('AARDVARK'));
$stub->with('bonobo')->generates()->throws(new RuntimeException('BONOBO'));
$stub->with('chameleon')->generates()->returns('CHAMELEON');
try {
    iterator_to_array($stub('aardvark'));
} catch (RuntimeException $e) {
}
try {
    iterator_to_array($stub('bonobo'));
} catch (RuntimeException $e) {
}
iterator_to_array($stub('chameleon'));

// verification
$stub->generated()->threw(new RuntimeException('DUGONG'));
