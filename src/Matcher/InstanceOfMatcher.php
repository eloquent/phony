<?php

declare(strict_types=1);

namespace Eloquent\Phony\Matcher;

use Eloquent\Phony\Exporter\Exporter;
use Eloquent\Phony\Matcher\Exception\UndefinedTypeException;

/**
 * A matcher that tests if the value is an instance of a given interface or
 * class.
 */
class InstanceOfMatcher implements Matcher
{
    /**
     * Construct a new instance of matcher.
     *
     * @param string $type The type to check against.
     */
    public function __construct(string $type)
    {
        $this->type = $type;

        $atoms = explode('\\', $type);

        /** @var string */
        $shortType = array_pop($atoms);
        $this->shortType = $shortType;
    }

    /**
     * Get the type.
     *
     * @return string The type.
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * Returns `true` if `$value` matches this matcher's criteria.
     *
     * @param mixed $value The value to check.
     *
     * @return bool True if the value matches.
     */
    public function matches($value): bool
    {
        if (!interface_exists($this->type) && !class_exists($this->type)) {
            throw new UndefinedTypeException($this->type);
        }

        return $value instanceof $this->type;
    }

    /**
     * Describe this matcher.
     *
     * @param ?Exporter $exporter The exporter to use.
     *
     * @return string The description.
     */
    public function describe(Exporter $exporter = null): string
    {
        return '<instanceof ' . $this->shortType . '>';
    }

    /**
     * Describe this matcher.
     *
     * @return string The description.
     */
    public function __toString(): string
    {
        return '<instanceof ' . $this->shortType . '>';
    }

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $shortType;
}
