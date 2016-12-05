<?php

use Eloquent\Phony\Test\Phony;

// setup
$spy = Phony::spy()->setLabel('label');
$spy(array(array(), array('+1', array()), (object) array('+1' => 1)));
$spy(array(array('+1', array()), (object) array('+1' => 1)));
$spy(array(array(1), (object) array('+1' => 1)));
$spy(array(array(1), array('+1'), (object) array('+1' => 1)));
$spy(array(array(1), array(array()), (object) array('+1' => 1)));
$spy(array(array(1), array('+1', array())));
$spy(array(array(1), array('+1', array()), array('+1' => 1)));
$spy(array(array(1), array('+1', array()), (object) array('1' => 1)));
$spy(array(array(1), array('+1', array()), (object) array('+1' => array())));

// verification
$spy->calledWith(array(array(1), array('+1', array()), (object) array('+1' => 1)));
