<?php

use Eloquent\Phony\Test\Phony;

// setup
$spy = Phony::spy()->setLabel('label');
$spy('aardvark', array('bonobo', 'capybara', 'dugong'));
$spy('armadillo', array('bonobo', 'chameleon', 'dormouse'));

// verification
$spy->lastCall()->calledWith('aardvark', array('bonobo', 'chameleon', 'dugong'));
