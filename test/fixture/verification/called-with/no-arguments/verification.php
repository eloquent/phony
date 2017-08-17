<?php

use Eloquent\Phony\Test\Phony;

// setup
$spy = Phony::spy()->setLabel('label');
$spy();
$spy('aardvark', ['bonobo', 'capybara', 'dugong']);
$spy();
$spy('armadillo', ['bonobo', 'chameleon', 'dormouse']);
$spy();

// verification
$spy->calledWith('aardvark', ['bonobo', 'chameleon', 'dugong']);
