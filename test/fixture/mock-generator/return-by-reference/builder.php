<?php

$builder = new Eloquent\Phony\Mock\Builder\MockBuilder(
    'Eloquent\Phony\Test\TestClassG'
);

return $builder->named('MockGeneratorReturnByReference');
