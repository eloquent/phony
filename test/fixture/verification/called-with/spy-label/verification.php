<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$spy = Phony::spy()->setLabel('label');
$spy('armadillo', ['bonobo', 'capybara', 'dugong']);

// verification
$spy->calledWith('aardvark', ['bonobo', 'chameleon', 'dugong']);
