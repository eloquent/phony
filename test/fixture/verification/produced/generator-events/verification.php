<?php

use Eloquent\Phony\Test\Phony;

// setup
$stub = Phony::stub()
    ->setLabel('label')
    ->does(
        function ($values) {
            foreach ($values as $key => $value) {
                try {
                    yield $key => $value;
                } catch (RuntimeException $e) {
                    throw $e;
                }
            }

            return count($values);
        }
    );
$stub(array('aardvark', 'bonobo'));
$generator = $stub(array('aardvark', 'bonobo'));
$generator->send('AARDVARK');
$generator->send('BONOBO');
$generator = $stub(array('aardvark'));
try {
    $generator->throw(new RuntimeException('AARDVARK'));
} catch (RuntimeException $e) {
}
$generator = $stub(array('aardvark', 'bonobo'));
foreach ($generator as $value) {
    break;
}

// verification
$stub->generated()->times(99)->produced();
