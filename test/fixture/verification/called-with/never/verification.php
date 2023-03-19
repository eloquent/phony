<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$spy = Phony::spy()->setLabel('label');
$spy(animal: 'aardvark', animals: ['bonobo', 'capybara', 'dugong']);
$spy(animal: 'aardvark', animals: ['bonobo', 'chameleon', 'dugong']);

// verification
$spy->never()->calledWith(animal: 'aardvark', animals: ['bonobo', 'chameleon', 'dugong']);
