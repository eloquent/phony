<?php

use Eloquent\Phony\Test\Phony;

// setup
$stub = null;
$stub = Phony::stub(
    function ($exception, $value) {
        if ($exception) {
            throw $exception;
        }

        return $value;
    }
)->setLabel('label')->forwards();
$stub(null, 'aardvark');
try {
    $stub(new RuntimeException('bonobo'), null);
} catch (RuntimeException $e) {
}

// verification
$stub->times(3)->responded();
