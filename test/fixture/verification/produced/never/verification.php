<?php

use Eloquent\Phony\Test\Phony;

// setup
$stub = Phony::stub()->setLabel('label')->setUseTraversableSpies(true);
$stub->with('aardvark')->returns(array('AARDVARK', 'ANTEATER'));
$stub->with('bonobo')->returns(array('BONOBO'));
iterator_to_array($stub('aardvark'));
$stub('bonobo');

// verification
$stub->traversed()->never()->produced('AARDVARK');
