<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$spy = Phony::spy()->setLabel('label');
$spy('aardvark', 'bonobo', 'dugong', 'chameleon');

// verification
$spy->calledWith('aardvark', 'bonobo', Phony::wildcard('chameleon', 1, -1));
