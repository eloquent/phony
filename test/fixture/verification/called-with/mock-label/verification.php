<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$handle = Phony::mock('ClassA')->setLabel('label');
$mock = $handle->get();
$mock->methodA(animal: 'armadillo', animals: ['bonobo', 'capybara', 'dugong']);

// verification
$handle->methodA->calledWith(animal: 'aardvark', animals: ['bonobo', 'chameleon', 'dugong']);
