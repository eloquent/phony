<?php

use Eloquent\Phony\Test\Phony;

// setup
$stub = Phony::stub()
    ->setLabel('label')
    ->setUseTraversableSpies(true)
    ->returns(array('aardvark', 'bonobo', 'chameleon'));
foreach ($stub() as $value) {
    if ('bonobo' === $value) {
        break;
    }
}
iterator_to_array($stub());

// verification
$stub->lastCall()->never()->completed();
