<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2017 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Test\Properties;

trait TestTrait
{
    public $traitPublic = '<trait-public>';
    private $traitPrivate = '<trait-private>';
    protected $traitProtected = '<trait-protected>';
}
