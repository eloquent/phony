<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Builder;

use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Mock\Handle\HandleFactory;
use Eloquent\Phony\Mock\MockFactory;

/**
 * Creates mock builders.
 */
class MockBuilderFactory
{
    /**
     * Get the static instance of this class.
     *
     * @return self The static instance.
     */
    public static function instance(): self
    {
        if (!self::$instance) {
            self::$instance = new self(
                MockFactory::instance(),
                HandleFactory::instance(),
                InvocableInspector::instance()
            );
        }

        return self::$instance;
    }

    /**
     * Construct a new mock builder factory.
     *
     * @param MockFactory        $mockFactory        The mock factory to use.
     * @param HandleFactory      $handleFactory      The handle factory to use.
     * @param InvocableInspector $invocableInspector The invocable inspector.
     */
    public function __construct(
        MockFactory $mockFactory,
        HandleFactory $handleFactory,
        InvocableInspector $invocableInspector
    ) {
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
            $this->mockFactory,
            $this->handleFactory,
            $this->invocableInspector
        );
    }

    /**
     * @var ?self
     */
    private static $instance;

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
