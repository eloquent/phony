<?php

declare(strict_types=1);

namespace Eloquent\Phony\Stub\Exception;

use Eloquent\Phony\Test\Facade\FacadeContainer;
use PHPUnit\Framework\TestCase;

class UnusedStubCriteriaExceptionTest extends TestCase
{
    public function testException()
    {
        $container = new FacadeContainer();
        $assertionRenderer = $container->assertionRenderer;
        $matcherFactory = $container->matcherFactory;
        $criteria = $matcherFactory->adaptSet([], [$matcherFactory->equalTo('a'), $matcherFactory->equalTo('b')]);
        $exception = new UnusedStubCriteriaException($criteria, $assertionRenderer);

        $this->assertSame($criteria, $exception->criteria());
        $this->assertSame(
            'Stub criteria \'0: "a", 1: "b"\' were never used. Check for incomplete stub rules.',
            $exception->getMessage()
        );
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
