<?php

namespace Eloquent\Phony\Test;

class TestExternalMatcher
{
    public function matches($value)
    {
        return 'value' === $value;
    }

    public function __toString()
    {
        return __CLASS__;
    }
}
