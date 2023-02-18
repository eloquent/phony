<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$spy = Phony::spy()->setLabel('label');
$spy('aardvark', ['bonobo', 'capybara', 'dugong']);
$spy('armadillo', ['bonobo', 'chameleon', 'dormouse']);
$spy('aardvark', ['bonobo', 'capybara', 'dugong']);

// verification
$spy->times(3)->calledWith('aardvark', ['bonobo', 'capybara', 'dugong']);
