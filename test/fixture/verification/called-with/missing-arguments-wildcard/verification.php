<?php

use Eloquent\Phony\Test\Phony;

// setup
$spy = Phony::spy()->setLabel('label');
$spy('aardvark');
$spy('aardvark', 'bonobo');
$spy('aardvark', 'bonobo', 'chameleon');
$spy('aardvark', 'bonobo', 'chameleon', 'dugong');

// verification
$spy->calledWith(
    'aardvark',
    'bonobo',
    'chameleon',
    Phony::wildcard('~', 99, 100)
);
