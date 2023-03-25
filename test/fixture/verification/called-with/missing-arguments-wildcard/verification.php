<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$spy = Phony::spy()->setLabel('label');
$spy(animalA: 'aardvark');
$spy(animalA: 'aardvark', animalB: 'bonobo');
$spy(animalA: 'aardvark', animalB: 'bonobo', animalC: 'chameleon');
$spy(animalA: 'aardvark', animalB: 'bonobo', animalC: 'chameleon', animalD: 'dugong');

// verification
$spy->calledWith(
    Phony::wildcard('~', 99, 100),
    animalA: 'aardvark',
    animalB: 'bonobo',
    animalC: 'chameleon',
);
