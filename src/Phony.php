<?php

declare(strict_types=1);

namespace Eloquent\Phony;

use Eloquent\Phony\Facade\FacadeTrait;
use Eloquent\Phony\Facade\Globals;

/**
 * A facade for standalone Phony usage.
 */
class Phony
{
    use FacadeTrait;

    /**
     * @var class-string
     */
    private static $globals = Globals::class;
}
