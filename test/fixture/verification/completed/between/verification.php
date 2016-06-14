<?php

use Eloquent\Phony\Test\Phony;

// setup
$stub = Phony::stub()
    ->setLabel('label')
    ->setUseTraversableSpies(true)
    ->returns(array('aardvark', 'bonobo', 'chameleon'));
iterator_to_array($stub());
iterator_to_array($stub());

// verification
$stub->between(3, 4)->completed();
