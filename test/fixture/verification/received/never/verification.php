<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$stub = Phony::stub()->setLabel('label')->setUseIterableSpies(true);
$stub->with('aardvark')->returns(['AARDVARK']);
$stub->with('bonobo')->generates(['BONOBO', 'BADGER']);
$stub->with('chameleon')->generates(['CHAMELEON']);
$stub->with('dugong')->returns(['CHAMELEON']);
iterator_to_array($stub('aardvark'));
$generator = $stub('bonobo');
$generator->send('MECHA-BONOBO');
$generator->send('MECHA-BADGER');
$stub('chameleon');
$stub('dugong');

// verification
$stub->generated()->never()->received('MECHA-BONOBO');
