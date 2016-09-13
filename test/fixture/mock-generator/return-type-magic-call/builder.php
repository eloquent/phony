<?php

$builder = $factory->create(
    array(
        'static __callStatic' =>
            function ($name, array $arguments) : \stdClass {},
        '__call' => function ($name, array $arguments) : \stdClass {},
    )
);

return $builder->named('MockGeneratorReturnTypeMagicCall');
