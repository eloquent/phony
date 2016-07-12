<?php

use Eloquent\Phony\Test\Phony;

// setup
$stub = null;
$stub = Phony::stub(
    function ($verify, $depth = 0) use (&$stub) {
        if ($depth < 1) {
            $stub($verify, $depth + 1);
        } else {
            $verify();
        }
    }
)->setLabel('label')->forwards();

// verification
$stub(
    function () use ($stub) {
        $stub->completed();
    }
);
