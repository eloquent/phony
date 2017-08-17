<?php

use Eloquent\Phony\Test\Phony;

// setup
$stub = Phony::stub()->setLabel('label')->setUseIterableSpies(true);
$stub->with('aardvark')->returns(['AARDVARK']);
$stub->with('bonobo')->returns(['BONOBO', 'BADGER']);
$stub('aardvark');
iterator_to_array($stub('bonobo'));

// verification
$stub->lastCall()->iterated()->times(3)->produced();
