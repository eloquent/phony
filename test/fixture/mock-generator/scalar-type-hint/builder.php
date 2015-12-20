<?php

return new Eloquent\Phony\Mock\Builder\MockBuilder(
    'Eloquent\Phony\Test\TestInterfaceWithScalarTypeHint',
    array('customMethod' => function (int $int) {}),
    'MockGeneratorScalarTypeHint'
);
