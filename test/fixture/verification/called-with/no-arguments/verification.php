<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$spy = Phony::spy()->setLabel('label');
$spy();
$spy(animal: 'aardvark', animals: ['bonobo', 'capybara', 'dugong']);
$spy();
$spy(animal: 'armadillo', animals: ['bonobo', 'chameleon', 'dormouse']);
$spy();

// verification
$spy->calledWith(animal: 'aardvark', animals: ['bonobo', 'chameleon', 'dugong']);
