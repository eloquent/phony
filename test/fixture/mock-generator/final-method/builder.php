<?php

$builder = new Eloquent\Phony\Mock\Builder\MockBuilder(
    'Eloquent\Phony\Test\TestClassF'
);

return $builder->named('Phony\Test\MockGeneratorFinalMethod');
