<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$spy = Phony::spy()->setLabel('label');
$spy('aardvark', ['bonobo', 'capybara', 'dugong']);
$spy('armadillo', ['bonobo', 'chameleon', 'dormouse']);

// verification
$spy->times(3)->called();
