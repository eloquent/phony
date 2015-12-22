<?php

$builder = new Eloquent\Phony\Mock\Builder\MockBuilder(
    array(
        'Eloquent\Phony\Test\TestInterfaceWithKeywordMethods',
        array(
            'throw' => function () {},
        ),
    )
);

return $builder->named('MockGeneratorRelaxedKeywords');
