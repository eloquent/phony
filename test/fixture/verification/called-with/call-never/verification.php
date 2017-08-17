<?php

use Eloquent\Phony\Test\Phony;

// setup
$spy = Phony::spy()->setLabel('label');
$spy('aardvark', ['bonobo', 'capybara', 'dugong']);
$spy('aardvark', ['bonobo', 'chameleon', 'dugong']);

// verification
$spy->lastCall()->never()->calledWith('aardvark', ['bonobo', 'chameleon', 'dugong']);
