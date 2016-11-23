<?php

$builder = $factory->create(
    'Eloquent\Phony\Test\TestInterfaceWithSelfReturnType'
);

return $builder->named('MockGeneratorSelfReturnType');
