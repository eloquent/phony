<?php

use Eloquent\Phony\Test\Phony;

// setup
$spy = Phony::spy()->setLabel('label');
$spy('armadillo', array('bonobo', 'capybara', 'dugong'));

// verification
$spy->calledWith('aardvark', array('bonobo', 'chameleon', 'dugong'));
