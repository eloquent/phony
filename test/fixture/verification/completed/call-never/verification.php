<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$stub = Phony::stub()
    ->setLabel('label')
    ->setUseIterableSpies(true)
    ->returns(['aardvark', 'bonobo', 'chameleon']);
foreach ($stub() as $value) {
    if ('bonobo' === $value) {
        break;
    }
}
iterator_to_array($stub());

// verification
$stub->lastCall()->never()->completed();
