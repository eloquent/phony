<?php

use Eloquent\Phony\Test\Phony;

// setup
$first = Phony::spy()->setLabel('first');
$first();

$stub = Phony::stub()
    ->setLabel('label')
    ->setUseTraversableSpies(true)
    ->returnsArgument();
$stub->with('c', 'd')->throws('C');
$stub('a', 'b');
try {
    $stub('c', 'd');
} catch (Exception $e) {
}
iterator_to_array($stub(array('e', 'f')));

// verification
Phony::inOrder(
    $stub->firstCall()->calledEvent(),
    $stub->returned(),
    $stub->threw(),
    $stub->traversed(),
    $stub->traversed()->used(),
    $stub->traversed()->produced(),
    $stub->traversed()->consumed(),
    $first->called()
);
