<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$spy = Phony::spy()->setLabel('label');
$spy([[], ['+1', []], (object) ['+1' => 1]]);
$spy([['+1', []], (object) ['+1' => 1]]);
$spy([[1], (object) ['+1' => 1]]);
$spy([[1], ['+1'], (object) ['+1' => 1]]);
$spy([[1], [[]], (object) ['+1' => 1]]);
$spy([[1], ['+1', []]]);
$spy([[1], ['+1', []], ['+1' => 1]]);
$spy([[1], ['+1', []], (object) ['1' => 1]]);
$spy([[1], ['+1', []], (object) ['+1' => []]]);

// verification
$spy->calledWith([[1], ['+1', []], (object) ['+1' => 1]]);
