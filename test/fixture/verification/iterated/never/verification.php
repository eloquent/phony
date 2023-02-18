<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$stub = Phony::stub()->setLabel('label')->setUseIterableSpies(true);
$stub->with('aardvark')->returns('AARDVARK');
$stub->with('bonobo')->returns(['BONOBO']);
$stub('aardvark');
$stub('bonobo');

// verification
$stub->never()->iterated();
