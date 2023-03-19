<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$spy = Phony::spy()->setLabel('label');
$spy(animal: 'aardvark', animals: ['bonobo', 'capybara', 'dugong']);
$spy(animal: 'armadillo', animals: ['bonobo', 'chameleon', 'dormouse']);
$spy(animal: 'aardvark', animals: ['bonobo', 'capybara', 'dugong']);

// verification
$spy->times(3)->calledWith(animal: 'aardvark', animals: ['bonobo', 'capybara', 'dugong']);
