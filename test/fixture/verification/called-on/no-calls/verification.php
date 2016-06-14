<?php

use Eloquent\Phony\Test\Phony;

// setup
$objectA = (object) array('name' => 'apricot');
$closure = function () {};
$spy = Phony::spy($closure)->setLabel('label');

// verification
$spy->calledOn($objectA);
