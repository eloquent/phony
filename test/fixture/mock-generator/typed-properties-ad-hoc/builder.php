<?php

$builder = $factory->create(
    [
        'propertyA' => 'a',
        'string propertyB' => 'b',
        'var ?int propertyC' => null,
    ]
);

return $builder->named('MockGeneratorTypedPropertiesAdHoc');
