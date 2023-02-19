<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Builder;

use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Mock\Handle\HandleFactory;
use Eloquent\Phony\Mock\MockFactory;
use Eloquent\Phony\Mock\MockGenerator;

/**
 * Creates mock builders.
 */
class MockBuilderFactory
{
    /**
     * Construct a new mock builder factory.
     *
     * @param MockGenerator      $mockGenerator      The mock generator to use.
     * @param MockFactory        $mockFactory        The mock factory to use.
     * @param HandleFactory      $handleFactory      The handle factory to use.
     * @param InvocableInspector $invocableInspector The invocable inspector.
     */
    public function __construct(
        MockGenerator $mockGenerator,
        MockFactory $mockFactory,
        HandleFactory $handleFactory,
        InvocableInspector $invocableInspector
    ) {
        $this->mockGenerator = $mockGenerator;
        $this->mockFactory = $mockFactory;
        $this->handleFactory = $handleFactory;
        $this->invocableInspector = $invocableInspector;
    }

    /**
     * Create a new mock builder.
     *
     * Each value in `$types` can be either a class name, or an ad hoc mock
     * definition. If only a single type is being mocked, the class name or
     * definition can be passed without being wrapped in an array.
     *
     * @param mixed $types The types to mock.
     *
     * @return MockBuilder The mock builder.
     */
    public function create($types = []): MockBuilder
    {
        return new MockBuilder(
            $types,
            $this->mockGenerator,
            $this->mockFactory,
            $this->handleFactory,
            $this->invocableInspector
        );
    }

    /**
     * @var MockGenerator
     */
    private $mockGenerator;

    /**
     * @var MockFactory
     */
    private $mockFactory;

    /**
     * @var HandleFactory
     */
    private $handleFactory;

    /**
     * @var InvocableInspector
     */
    private $invocableInspector;
}
