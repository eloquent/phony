<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Handle\Stubbing;

use Eloquent\Phony\Mock\Handle\InstanceHandleInterface;

/**
 * The interface implemented by instance stubbing handles.
 *
 * @api
 */
interface InstanceStubbingHandleInterface extends InstanceHandleInterface,
    StubbingHandleInterface
{
}
