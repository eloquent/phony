<?php

use Eloquent\Phony\Test\Phony;

// setup
$stub = Phony::stub()
    ->setLabel('label')
    ->setUseIterableSpies(true)
    ->returns(['aardvark', 'bonobo', 'chameleon']);
iterator_to_array($stub());
iterator_to_array($stub());

// verification
$stub->times(3)->completed();
