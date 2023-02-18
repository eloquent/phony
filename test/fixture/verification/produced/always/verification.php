<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$stub = Phony::stub()->setLabel('label')->setUseIterableSpies(true);
$stub->with('aardvark')->returns(['AARDVARK', 'ANTEATER']);
$stub->with('bonobo')->returns(['BONOBO']);
iterator_to_array($stub('aardvark'));
$stub('bonobo');

// verification
$stub->iterated()->always()->produced('AARDVARK');
