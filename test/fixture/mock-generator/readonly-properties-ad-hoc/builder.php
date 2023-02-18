<?php

$builder = $factory->create(
    [
        'readonly string propertyA' => null,
        'var readonly ?int propertyB' => null,
    ]
);

return $builder->named('MockGeneratorReadonlyPropertiesAdHoc');
