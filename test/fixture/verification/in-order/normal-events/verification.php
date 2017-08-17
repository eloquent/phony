<?php

use Eloquent\Phony\Test\Phony;

// setup
$first = Phony::spy()->setLabel('first');
$first();

$stub = Phony::stub()
    ->setLabel('label')
    ->setUseIterableSpies(true)
    ->returnsArgument();
$stub->with('c', 'd')->throws('C');
$stub('a', 'b');
try {
    $stub('c', 'd');
} catch (Exception $e) {
}
iterator_to_array($stub(['e', 'f']));

// verification
Phony::inOrder(
    $stub->firstCall()->calledEvent(),
    $stub->returned(),
    $stub->threw(),
    $stub->iterated(),
    $stub->iterated()->used(),
    $stub->iterated()->produced(),
    $stub->iterated()->consumed(),
    $first->called()
);
