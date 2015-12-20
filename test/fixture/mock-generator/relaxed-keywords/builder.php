<?php

return new Eloquent\Phony\Mock\Builder\MockBuilder(
    'Eloquent\Phony\Test\TestInterfaceWithKeywordMethods',
    array(
        'throw' => function () {},
    ),
    'MockGeneratorRelaxedKeywords'
);
