<?php

use Eloquent\Phony\Test\Phony;

// setup
$builder = Phony::mockBuilder()
    ->like(
        array(
            'methodA' => function () {},
            'methodB' => function () {},
        )
    )
    ->named('PhonyNoInteraction');
$mock = $builder->get();
$handle = Phony::on($mock)->setLabel('label');
$mock->methodA('b', 'c');
$mock->methodB('c', 'd');

// verification
$handle->noInteraction();
