<?php

use Eloquent\Phony\Test\Phony;

// setup
$spy = Phony::spy()->setLabel('label');
$spy('aardvark', array('bonobo', 'capybara', 'dugong'));
$spy('aardvark', array('bonobo', 'chameleon', 'dugong'));

// verification
$spy->never()->calledWith('aardvark', array('bonobo', 'chameleon', 'dugong'));
