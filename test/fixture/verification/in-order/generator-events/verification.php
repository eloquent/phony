<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$first = Phony::spy()->setLabel('first');
$first();

$stub = Phony::stub()->setLabel('label')->does(
    function () {
        yield 'a';

        try {
            yield 'b';
        } catch (Exception $e) {
        }
    }
);
$generator = $stub();
$generator->send('A');
$generator->throw(new Exception('B'));

// verification
Phony::inOrder(
    $stub->generated(),
    $stub->generated()->produced('a'),
    $stub->generated()->received('A'),
    $stub->generated()->produced('b'),
    $stub->generated()->receivedException(new Exception('B')),
    $first->called()
);
