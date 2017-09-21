<?php

declare(strict_types=1);

namespace Eloquent\Phony\Stub\Exception;

use Eloquent\Phony\Matcher\MatcherFactory;
use PHPUnit\Framework\TestCase;

class UnusedStubCriteriaExceptionTest extends TestCase
{
    public function testException()
    {
        $matcherFactory = MatcherFactory::instance();
        $criteria = [$matcherFactory->equalTo('a'), $matcherFactory->equalTo('b')];
        $exception = new UnusedStubCriteriaException($criteria);

        $this->assertSame($criteria, $exception->criteria());
        $this->assertSame(
            'Stub criteria \'"a", "b"\' were never used. Check for incomplete stub rules.',
            $exception->getMessage()
        );
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
