<?php

$builder = $factory->create(
    [
        'Eloquent\Phony\Test\TestInterfaceWithKeywordMethods',
        [
            'throw' => function () {},
        ],
    ]
);

return $builder->named('MockGeneratorRelaxedKeywords');
