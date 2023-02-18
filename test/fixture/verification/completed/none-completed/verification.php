<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$stub = Phony::stub()
    ->setLabel('label')
    ->setUseIterableSpies(true)
    ->returnsArgument();
$stub(['aardvark', 'bonobo', 'chameleon']);
foreach ($stub(['aardvark', 'bonobo', 'chameleon']) as $value) {
    if ('bonobo' === $value) {
        break;
    }
}

// verification
$stub->completed();
