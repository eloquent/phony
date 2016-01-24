<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Exception;

use Exception;

/**
 * An invalid definition was encountered.
 */
final class InvalidDefinitionException extends Exception implements
    MockExceptionInterface
{
    /**
     * Construct a new invalid definition exception.
     *
     * @param mixed          $name  The name.
     * @param mixed          $value The value.
     * @param Exception|null $cause The cause, if available.
     */
    public function __construct($name, $value, Exception $cause = null)
    {
        $this->name = $name;
        $this->value = $value;

        parent::__construct(
            sprintf(
                'Invalid mock definition %s: (%s).',
                var_export($name, true),
                gettype($value)
            ),
            0,
            $cause
        );
    }

    /**
     * Get the name.
     *
     * @return mixed The name.
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * Get the value.
     *
     * @return mixed The value.
     */
    public function value()
    {
        return $this->value;
    }

    private $name;
    private $value;
}
