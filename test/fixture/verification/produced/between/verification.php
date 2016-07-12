<?php

use Eloquent\Phony\Test\Phony;

// setup
$stub = Phony::stub()->setLabel('label')->setUseIterableSpies(true);
$stub->with('aardvark')->returns(array('AARDVARK', 'MECHA-AARDVARK'));
$stub->with('bonobo')->returns(array('BONOBO', 'MECHA-BONOBO'));
iterator_to_array($stub('aardvark'));
iterator_to_array($stub('bonobo'));

// verification
$stub->iterated()->between(3, 4)->produced();
