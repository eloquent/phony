<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

use Eloquent\Phony\Facade\FacadeTrait;
use Eloquent\Phony\Facade\Globals;

/**
 * A facade for Phony integration tests.
 */
class Phony
{
    use FacadeTrait;

    public static function reset()
    {
        Globals::$container->exporter->reset();

        foreach (Globals::$container->sequences as $sequence) {
            $sequence->reset();
        }
    }

    private static $globals = Globals::class;
}
