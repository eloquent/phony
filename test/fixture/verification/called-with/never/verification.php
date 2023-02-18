<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$spy = Phony::spy()->setLabel('label');
$spy('aardvark', ['bonobo', 'capybara', 'dugong']);
$spy('aardvark', ['bonobo', 'chameleon', 'dugong']);

// verification
$spy->never()->calledWith('aardvark', ['bonobo', 'chameleon', 'dugong']);
