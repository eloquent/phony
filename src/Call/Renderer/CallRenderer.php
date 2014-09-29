<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Renderer;

use Eloquent\Phony\Call\CallInterface;
use ReflectionMethod;
use SebastianBergmann\Exporter\Exporter;

/**
 * Renders calls.
 *
 * @internal
 */
class CallRenderer implements CallRendererInterface
{
    /**
     * Get the static instance of this renderer.
     *
     * @return CallRendererInterface The static factory.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Construct a new call renderer.
     *
     * @param Exporter|null $exporter The exporter to use.
     */
    public function __construct(Exporter $exporter = null)
    {
        if (null === $exporter) {
            $exporter = new Exporter();
        }

        $this->exporter = $exporter;
    }

    /**
     * Get the exporter.
     *
     * @return Exporter The exporter.
     */
    public function exporter()
    {
        return $this->exporter;
    }

    /**
     * Render the supplied call.
     *
     * @param CallInterface $call The call.
     *
     * @return string The rendered call.
     */
    public function render(CallInterface $call)
    {
        $subject = $call->subject();

        if ($subject instanceof ReflectionMethod) {
            if ($subject->isStatic()) {
                $callOperator = '::';
            } else {
                $callOperator = '->';
            }

            $renderedSubject = $subject->getDeclaringClass()->getName() .
                $callOperator .
                $subject->getName();
        } else {
            $renderedSubject = $subject->getName();
        }

        $arguments = $call->arguments();

        $renderedArguments = array();
        foreach ($arguments as $argument) {
            $renderedArguments[] = $this->exporter->shortenedExport($argument);
        }

        return sprintf(
            '%s(%s)',
            $renderedSubject, implode(', ', $renderedArguments)
        );
    }

    private static $instance;
}
