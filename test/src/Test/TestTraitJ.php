<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2017 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Test;

trait TestTraitJ
{
    public static function __callStatic($name, array $arguments)
    {
        return 'magic ' . $name . ' ' . implode($arguments);
    }

    public function __call($name, array $arguments)
    {
        return 'magic ' . $name . ' ' . implode($arguments);
    }
}
