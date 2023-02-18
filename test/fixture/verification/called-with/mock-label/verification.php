<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$handle = Phony::mock('ClassA')->setLabel('label');
$mock = $handle->get();
$mock->methodA('armadillo', ['bonobo', 'capybara', 'dugong']);

// verification
$handle->methodA->calledWith('aardvark', ['bonobo', 'chameleon', 'dugong']);
