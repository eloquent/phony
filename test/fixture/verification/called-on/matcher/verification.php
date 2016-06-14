<?php

use Eloquent\Phony\Test\Phony;

// setup
$objectA = (object) array('name' => 'apricot');
$objectB = (object) array('name' => 'banana');
$closure = function () {};
$closure = $closure->bindTo($objectA);
$spy = Phony::spy($closure)->setLabel('label');
$spy('aardvark', array('bonobo', 'capybara', 'dugong'));
$spy('bonobo', array('chameleon', 'dormouse', 'earwig'));

// verification
$spy->calledOn(Phony::equalTo($objectB));
