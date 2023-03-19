<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$spy = Phony::spy()->setLabel('label');
$spy(animal: 'aardvark', animals: ['bonobo', 'capybara', 'dugong']);
$spy(animal: 'armadillo', animals: ['bonobo', 'chameleon', 'dormouse']);

// verification
$spy->between(3, 4)->called();
