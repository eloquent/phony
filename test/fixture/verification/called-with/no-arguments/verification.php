<?php

use Eloquent\Phony\Test\Phony;

// setup
$spy = Phony::spy()->setLabel('label');
$spy();
$spy('aardvark', array('bonobo', 'capybara', 'dugong'));
$spy();
$spy('armadillo', array('bonobo', 'chameleon', 'dormouse'));
$spy();

// verification
$spy->calledWith('aardvark', array('bonobo', 'chameleon', 'dugong'));
