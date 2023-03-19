<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$spy = Phony::spy()->setLabel('label');
$spy(animalA: 'aardvark');
$spy(animalA: 'aardvark', animalB: 'bonobo');
$spy(animalA: 'aardvark', animalB: 'bonobo', animalC: 'chameleon');

// verification
$spy->calledWith(animalA: 'aardvark', animalB: 'bonobo', animalC: 'chameleon', animalD: 'dugong');
