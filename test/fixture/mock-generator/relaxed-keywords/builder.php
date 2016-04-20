<?php

$builder = $factory->create(
    array(
        'Eloquent\Phony\Test\TestInterfaceWithKeywordMethods',
        array(
            'throw' => function () {},
        ),
    )
);

return $builder->named('MockGeneratorRelaxedKeywords');
