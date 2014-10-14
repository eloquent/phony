<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Builder\Exception;

use Exception;

/**
 * Unable to add the supplied type.
 *
 * @internal
 */
final class InvalidTypeException extends Exception implements
    MockBuilderExceptionInterface
{
    /**
     * Construct an invalid type exception.
     *
     * @param mixed          $type  The type.
     * @param Exception|null $cause The cause, if available.
     */
    public function __construct($type, Exception $cause = null)
    {
        $this->type = $type;

        parent::__construct(
            sprintf(
                'Unable to add type of type %s.',
                var_export(gettype($type), true)
            ),
            0,
            $cause
        );
    }

    /**
     * Get the type.
     *
     * @return mixed The type.
     */
    public function type()
    {
        return $this->type;
    }

    private $type;
}
