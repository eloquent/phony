<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$spy = Phony::spy()->setLabel('label');
$spy('aardvark');
$spy('aardvark', 'bonobo');
$spy('aardvark', 'bonobo', 'chameleon');

// verification
$spy->calledWith('aardvark', 'bonobo', 'chameleon', 'dugong');
