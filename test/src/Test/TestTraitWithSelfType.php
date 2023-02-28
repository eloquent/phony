<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

trait TestTraitWithSelfType
{
    abstract public static function staticMethod(self $a): self;

    abstract public function method(self $a): self;
}
