<?php

use Eloquent\Phony\Test\Phony;

// setup
$spyA = Phony::spy()->setLabel('a');
$spyB = Phony::spy()->setLabel('b');

$spyA(1);
$spyA(2);

$spyB(1);

$spyA(3);

$spyB(2);
$spyB(3);

// verification
Phony::inOrderSequence(
    [
        $spyA->calledWith(1),
        $spyA->calledWith(2),
        $spyA->calledWith(3),
        $spyB->calledWith(1),
        $spyB->calledWith(2),
        $spyB->calledWith(3),
    ]
);
