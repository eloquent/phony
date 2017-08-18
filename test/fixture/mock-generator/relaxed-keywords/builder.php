<?php

use Eloquent\Phony\Test\TestInterfaceWithKeywordMethods;

$builder = $factory->create(
    [
        TestInterfaceWithKeywordMethods::class,
        [
            'throw' => function () {},
        ],
    ]
);

return $builder->named('MockGeneratorRelaxedKeywords');
