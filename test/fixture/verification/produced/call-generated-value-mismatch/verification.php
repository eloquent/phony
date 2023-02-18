<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$stub = Phony::stub()->setLabel('label')->setUseIterableSpies(true);
$stub->with('aardvark')->generates(['AARDVARK']);
$stub->with('bonobo')->generates(['BONOBO']);
iterator_to_array($stub('aardvark'));
iterator_to_array($stub('bonobo'));

// verification
$stub->lastCall()->generated()->produced('CHAMELEON');
