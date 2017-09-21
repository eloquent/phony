<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

use Eloquent\Phony\Hook\FunctionHookGenerator;

class TestFunctionHookGenerator extends FunctionHookGenerator
{
    public function __construct($source)
    {
        $this->source = $source;
    }

    public function generateHook(
        string $name,
        string $namespace,
        array $signature
    ): string {
        return $this->source;
    }

    private $source;
}
