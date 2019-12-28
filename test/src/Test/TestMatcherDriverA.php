<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

use Eloquent\Phony\Exporter\InlineExporter;
use Eloquent\Phony\Matcher\EqualToMatcher;
use Eloquent\Phony\Matcher\Matcher;
use Eloquent\Phony\Matcher\MatcherDriver;

class TestMatcherDriverA implements MatcherDriver
{
    public function isAvailable(): bool
    {
        return true;
    }

    public function matcherClassNames(): array
    {
        return [TestMatcherA::class];
    }

    public function wrapMatcher($matcher): Matcher
    {
        return new EqualToMatcher('a', false, InlineExporter::instance());
    }
}
