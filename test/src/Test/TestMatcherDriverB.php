<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

use Eloquent\Phony\Exporter\Exporter;
use Eloquent\Phony\Matcher\EqualToMatcher;
use Eloquent\Phony\Matcher\Matcher;
use Eloquent\Phony\Matcher\MatcherDriver;

class TestMatcherDriverB implements MatcherDriver
{
    public function __construct(Exporter $exporter)
    {
        $this->exporter = $exporter;
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function matcherClassNames(): array
    {
        return [TestMatcherB::class];
    }

    public function wrapMatcher($matcher): Matcher
    {
        return new EqualToMatcher('b', false, $this->exporter);
    }

    private Exporter $exporter;
}
