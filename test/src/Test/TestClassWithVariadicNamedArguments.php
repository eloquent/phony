<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

class TestClassWithVariadicNamedArguments
{
    public static array $staticArguments;

    public static function setStaticArguments($a, $b, ...$arguments)
    {
        self::$staticArguments = [$a, $b, ...$arguments];
    }

    public array $arguments;

    public function setArguments($a, $b, ...$arguments)
    {
        $this->arguments = [$a, $b, ...$arguments];
    }
}
