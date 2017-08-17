<?php

$builder = $factory->create(
    [
        'const CONSTANT_A' => [],
        'const CONSTANT_B' => ['a', 'b'],
        'const CONSTANT_C' => ['a' => 'b', 'c' => 'd'],
    ]
);

return $builder->named('MockGeneratorArrayConstant');
