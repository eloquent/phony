<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call\Exception;

/**
 * The interface implemented by undefined argument exceptions.
 */
interface UndefinedArgumentException
{
    /**
     * Returns true if the undefined argument was requested by name.
     *
     * @return bool True if the undefined argument was requested by name.
     */
    public function isNamed(): bool;
}
