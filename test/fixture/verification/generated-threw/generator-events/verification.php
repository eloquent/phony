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
$stub(['aardvark', 'bonobo']);
$generator = $stub(['aardvark', 'bonobo']);
$generator->send('AARDVARK');
$generator->send('BONOBO');
$generator = $stub(['aardvark']);
try {
    $generator->throw(new RuntimeException('AARDVARK'));
} catch (RuntimeException $e) {
}
$generator = $stub(['aardvark', 'bonobo']);
foreach ($generator as $value) {
    break;
}

// verification
$stub->generated()->threw('InvalidArgumentException');
