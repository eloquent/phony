<?php

use Eloquent\Phony\Test\Phony;

// setup
$objectA = (object) array('name' => 'apricot');
$closure = function () {};
$closure = $closure->bindTo($objectA);
$spy = Phony::spy($closure)->setLabel('label');
$spy('aardvark', array('bonobo', 'capybara', 'dugong'));
$spy('bonobo', array('chameleon', 'dormouse', 'earwig'));

// verification
$spy->times(3)->calledOn($objectA);
