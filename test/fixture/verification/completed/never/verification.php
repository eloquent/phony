<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$stub = Phony::stub()
    ->setLabel('label')
    ->setUseIterableSpies(true)
    ->returns('aardvark', ['aardvark', 'bonobo', 'chameleon'])
    ->with('earwig')->throws(new RuntimeException('EARWIG'));
$stub();
try {
    $stub('earwig');
} catch (RuntimeException $e) {
}
iterator_to_array($stub());
foreach ($stub() as $value) {
    if ('bonobo' === $value) {
        break;
    }
}
$stub();
iterator_to_array($stub());

// verification
$stub->never()->completed();
