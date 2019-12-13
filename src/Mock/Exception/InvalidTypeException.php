<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Exception;

use Exception;
use Throwable;

/**
 * Unable to add the supplied type.
 */
final class InvalidTypeException extends Exception implements
    MockException
{
    /**
     * Construct a new invalid type exception.
     *
     * @param mixed      $type  The type.
     * @param ?Throwable $cause The cause, if available.
     */
    public function __construct($type, Throwable $cause = null)
    {
        $this->type = $type;

        if (is_string($type)) {
            $message = sprintf('Undefined type %s.', var_export($type, true));
        } else {
            $message = sprintf(
                'Unable to add type of type %s.',
                var_export(gettype($type), true)
            );
        }

        parent::__construct($message, 0, $cause);
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

    /**
     * @var mixed
     */
    private $type;
}
