<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$stub = Phony::stub()->setLabel('label')->setUseIterableSpies(true);
$stub->with('aardvark')->generates()->returns('AARDVARK');
$stub->with('bonobo')->generates()->returns('BONOBO');
iterator_to_array($stub('aardvark'));
iterator_to_array($stub('bonobo'));

// verification
$stub->lastCall()->generated()->returned('DUGONG');
