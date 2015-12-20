<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Invocation;

use Eloquent\Phony\Call\Argument\ArgumentsInterface;
use Error;
use Exception;

/**
 * The interface implemented by invocables.
 *
 * @api
 */
interface InvocableInterface
{
    /**
     * Invoke this object.
     *
     * This method supports reference parameters.
     *
     * @api
     *
     * @param ArgumentsInterface|array The arguments.
     *
     * @return mixed           The result of invocation.
     * @throws Exception|Error If an error occurs.
     */
    public function invokeWith($arguments = array());

    /**
     * Invoke this object.
     *
     * @api
     *
     * @param mixed ...$arguments The arguments.
     *
     * @return mixed           The result of invocation.
     * @throws Exception|Error If an error occurs.
     */
    public function invoke();

    /**
     * Invoke this object.
     *
     * @api
     *
     * @param mixed ...$arguments The arguments.
     *
     * @return mixed           The result of invocation.
     * @throws Exception|Error If an error occurs.
     */
    public function __invoke();
}
