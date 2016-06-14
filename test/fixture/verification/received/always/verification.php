<?php

use Eloquent\Phony\Test\Phony;

// setup
$stub = Phony::stub()->setLabel('label')->setUseTraversableSpies(true);
$stub->with('aardvark')->returns(array('AARDVARK'));
$stub->with('bonobo')->generates(array('BONOBO', 'BADGER'));
$stub->with('chameleon')->generates(array('CHAMELEON'));
iterator_to_array($stub('aardvark'));
$generator = $stub('bonobo');
$generator->send('MECHA-BONOBO');
$generator->send('MECHA-BADGER');
iterator_to_array($stub('chameleon'));

// verification
$stub->generated()->always()->received('MECHA-BONOBO');
