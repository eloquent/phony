<?php

$builder = $factory->create(
    array(
        'const CONSTANT_A' => array(),
        'const CONSTANT_B' => array('a', 'b'),
        'const CONSTANT_C' => array('a' => 'b', 'c' => 'd'),
    )
);

return $builder->named('MockGeneratorArrayConstant');
