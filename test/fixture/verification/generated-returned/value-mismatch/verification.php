<?php

use Eloquent\Phony\Test\Phony;

// setup
$stub = Phony::stub()->setLabel('label')->setUseIterableSpies(true);
$stub->with('aardvark')->generates()->returns('AARDVARK');
$stub->with('bonobo')->generates()->returns('BONOBO');
$stub->with('chameleon')->generates()->throws(new RuntimeException('CHAMELEON'));
iterator_to_array($stub('aardvark'));
iterator_to_array($stub('bonobo'));
try {
    iterator_to_array($stub('chameleon'));
} catch (RuntimeException $e) {
}

// verification
$stub->generated()->returned('DUGONG');
