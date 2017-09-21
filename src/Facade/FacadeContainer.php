<?php

declare(strict_types=1);

namespace Eloquent\Phony\Facade;

use Eloquent\Phony\Assertion\ExceptionAssertionRecorder;

/**
 * A service container for Phony facades.
 */
class FacadeContainer
{
    use FacadeContainerTrait;

    public function __construct()
    {
        $this->initializeContainer(new ExceptionAssertionRecorder());
    }
}
