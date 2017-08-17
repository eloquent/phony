<?php

use Eloquent\Phony\Test\Phony;

// setup
$stub = Phony::stub()
    ->setLabel('label')
    ->setUseIterableSpies(true)
    ->returns(['aardvark', 'bonobo', 'chameleon']);
iterator_to_array($stub());
foreach ($stub() as $value) {
    if ('bonobo' === $value) {
        break;
    }
}
$stub();
iterator_to_array($stub());

// verification
$stub->always()->completed();
