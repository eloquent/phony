<?php

use Eloquent\Phony\Test\Phony;

// setup
$stub = Phony::stub()->setLabel('label')->setUseIterableSpies(true);
$stub->with('aardvark')->returns(['AARDVARK' => 'MECHA-AARDVARK']);
$stub->with('bonobo')->returns(['BONOBO' => 'MECHA-BONOBO']);
iterator_to_array($stub('aardvark'));
iterator_to_array($stub('bonobo'));

// verification
$stub->iterated()->produced('CHAMELEON', 'MECHA-BONOBO');
