<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$handle = Phony::mock('ClassA')->setLabel('label');
$class = $handle->className();
$class::staticMethodA('b', 'c');
$class::staticMethodB('c', 'd');

// verification
Phony::onStatic($handle)->noInteraction();
