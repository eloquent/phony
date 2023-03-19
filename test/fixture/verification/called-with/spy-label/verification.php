<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$spy = Phony::spy()->setLabel('label');
$spy(animal: 'armadillo', animals: ['bonobo', 'capybara', 'dugong']);

// verification
$spy->calledWith(animal: 'aardvark', animals: ['bonobo', 'chameleon', 'dugong']);
