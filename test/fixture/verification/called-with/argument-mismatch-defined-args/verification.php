<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$spy = Phony::spy('implode')->setLabel('label');
$spy('aardvark', ['bonobo', 'capybara', 'dugong']);
$spy('armadillo', array: ['bonobo', 'chameleon', 'dormouse']);

// verification
$spy->calledWith(array: ['bonobo', 'chameleon', 'dugong'], separator: 'aardvark');
