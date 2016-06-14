<?php

use Eloquent\Phony\Test\Phony;

// setup
$spy = Phony::spy()->setLabel('label');

// verification
$spy->called();
