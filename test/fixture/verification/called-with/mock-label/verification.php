<?php

use Eloquent\Phony\Test\Phony;

// setup
$handle = Phony::mock('ClassA')->setLabel('label');
$mock = $handle->get();
$mock->methodA('armadillo', array('bonobo', 'capybara', 'dugong'));

// verification
$handle->methodA->calledWith('aardvark', array('bonobo', 'chameleon', 'dugong'));
