<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock;

use Eloquent\Phony\Mock\Builder\MockDefinition;

/**
 * Stores information about mocked classes.
 */
class MockRegistry
{
    /**
     * @var array<class-string,MockDefinition>
     */
    public $definitions = [];
}
