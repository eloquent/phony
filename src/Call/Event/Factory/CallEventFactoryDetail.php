<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Event\Factory;

use Generator;

/**
 * A detail class for generator syntax not supported by earlier versions of PHP.
 *
 * @internal
 */
abstract class CallEventFactoryDetail
{
    /**
     * Create a new empty generator.
     *
     * @return Generator The newly created generator.
     */
    public static function createEmptyGenerator()
    {
        return call_user_func(
            function () { // @codeCoverageIgnoreStart

                return;
                yield null;
            } // @codeCoverageIgnoreEnd
        );
    }
}
