<?php

use Eloquent\Phony\Test\Phony;

// setup
$spy = Phony::spy()->setLabel('label');
$spy('aardvark', ['bonobo', 'capybara', 'dugong']);
$spy('armadillo', ['bonobo', 'chameleon', 'dormouse']);

// verification
$spy->lastCall()->calledWith('aardvark', ['bonobo', 'chameleon', 'dugong']);
