<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$spy = Phony::spy()->setLabel('label');
$spy();
$spy('aardvark', ['bonobo', 'capybara', 'dugong']);
$spy();
$spy('armadillo', ['bonobo', 'chameleon', 'dormouse']);
$spy();

// verification
$spy->never()->called();
