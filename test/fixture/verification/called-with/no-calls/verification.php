<?php

use Eloquent\Phony\Test\Phony;

// setup
$spy = Phony::spy()->setLabel('label');

// verification
$spy->calledWith('aardvark', ['bonobo', 'chameleon', 'dugong']);
