<?php

declare(strict_types=1);

namespace Eloquent\Phony;

use Eloquent\Phony\Facade\FacadeDriver;
use Eloquent\Phony\Facade\FacadeTrait;

/**
 * A facade for standalone Phony usage.
 */
class Phony
{
    use FacadeTrait;

    private static function driver()
    {
        return FacadeDriver::instance();
    }
}
