<?php

use Eloquent\Phony\Test\Facade\Phony;

// setup
$stub = Phony::stub()->setLabel('label')->setUseIterableSpies(true);
$stub->with('aardvark')->returns('AARDVARK');
$stub->with('bonobo')->throws(new RuntimeException('BONOBO'));
$stub->with('chameleon')->returns(['CHAMELEON']);
$stub->with('dugong')->returns(['DUGONG', 'MECHA-DUGONG']);
$stub->with('earwig')->returns(['EARWIG']);
$stub->with('ferret')->generates(['FERRET']);
$stub->with('gibbon')->generates()->throws(new RuntimeException('GIBBON'));
$stub->with('hippopotamus')->generates(['HIPPOPOTAMUS', 'MECHA-HIPPOPOTAMUS']);
$stub('aardvark');
try {
    $stub('bonobo');
} catch (RuntimeException $e) {
}
iterator_to_array($stub('chameleon'));
foreach ($stub('dugong') as $value) {
    break;
}
$stub('earwig');
iterator_to_array($stub('ferret'));
try {
    iterator_to_array($stub('gibbon'));
} catch (RuntimeException $e) {
}
foreach ($stub('hippopotamus') as $value) {
    break;
}

// verification
$stub->generated()->never()->consumed();
