<?php

declare(strict_types=1);

$definition = join("\n", [
    'namespace Eloquent\Phony\Test;',
    'class TestClassWithTypedProperties {',
    '    public int $int;',
    '    public string $string;',
    '}',
]);

eval($definition);
