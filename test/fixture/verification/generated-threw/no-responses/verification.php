<?php

use Eloquent\Phony\Test\Phony;

// setup
$stub = Phony::stub()->setUseIterableSpies(true)->setLabel('label');
$stub->with('aardvark')->generates();
$stub->with('bonobo', '*')->does(
    function ($animal, $verify, $depth = 0) use (&$stub) {
        if ($depth < 1) {
            iterator_to_array($stub($animal, $verify, $depth + 1));
        } else {
            $verify();
        }

        return [];
    }
);
iterator_to_array($stub('aardvark'));

// verification
iterator_to_array(
    $stub(
        'bonobo',
        function () use ($stub) {
            $stub->generated()->threw('InvalidArgumentException');
        }
    )
);
