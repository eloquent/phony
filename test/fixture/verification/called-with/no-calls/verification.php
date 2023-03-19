<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$spy = Phony::spy()->setLabel('label');

// verification
$spy->calledWith(animal: 'aardvark', animals: ['bonobo', 'chameleon', 'dugong']);
