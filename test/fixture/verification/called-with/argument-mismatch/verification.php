<?php

use Eloquent\Phony\Test\Phony;

// setup
$spy = Phony::spy()->setLabel('label');
$spy('aardvark', ['bonobo', 'capybara', 'dugong']);
$spy('armadillo', ['bonobo', 'chameleon', 'dormouse']);

// verification
$spy->calledWith('aardvark', ['bonobo', 'chameleon', 'dugong']);
