<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$handle = Phony::mock('ClassA')->setLabel('label');
$mock = $handle->get();
$mock->methodA('b', 'c');
$mock->methodB('c', 'd');

// verification
$handle->noInteraction();
