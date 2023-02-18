<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$builder = Phony::mockBuilder()
    ->like(
        [
            'methodA' => function () {},
            'methodB' => function () {},
        ]
    )
    ->named('PhonyNoInteraction');
$mock = $builder->get();
$handle = Phony::on($mock)->setLabel('label');
$mock->methodA('b', 'c');
$mock->methodB('c', 'd');

// verification
$handle->noInteraction();
