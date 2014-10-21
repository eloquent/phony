<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Exception;

use Eloquent\Phony\Mock\Builder\MockBuilderInterface;
use Exception;

/**
 * Mock generation failed.
 *
 * @internal
 */
final class MockGenerationFailedException extends Exception implements
    MockExceptionInterface
{
    /**
     * Construct a mock generation failed exception.
     *
     * @param MockBuilderInterface     $mockBuilder The mock builder.
     * @param string                   $source      The generated source code.
     * @param array<string,mixed>|null $error       The error details.
     * @param Exception|null           $cause       The cause, if available.
     */
    public function __construct(
        MockBuilderInterface $mockBuilder,
        $source,
        array $error = null,
        Exception $cause = null
    ) {
        $this->mockBuilder = $mockBuilder;
        $this->source = $source;
        $this->error = $error;

        $isHhvm = defined('HHVM_VERSION');
        $lines = explode("\n", $source);

        if ($isHhvm) { // @codeCoverageIgnoreStart
            $message = sprintf(
                "Mock class %s generation failed.\nRelevant lines:%%s",
                $mockBuilder->className()
            );
        } else { // @codeCoverageIgnoreEnd
            $errorLineNumber = $error['line'];
            $startLine = $errorLineNumber - 4;
            $contextLineCount = 7;

            if ($startLine < 0) {
                $contextLineCount += $startLine;
                $startLine = 0;
            }

            $lines = array_slice($lines, $startLine, $contextLineCount, true);

            $message = sprintf(
                "Mock class %s generation failed: " .
                    "%s in generated code on line %d.\n" .
                    "Relevant lines:%%s",
                $mockBuilder->className(),
                $error['message'],
                $errorLineNumber
            );
        }

        foreach ($lines as $lineNumber => $line) {}

        $padSize = strlen($lineNumber + 1) + 4;
        $renderedLines = '';

        foreach ($lines as $lineNumber => $line) {
            $renderedLines .= sprintf(
                "\n%s: %s",
                str_pad($lineNumber + 1, $padSize, ' ', STR_PAD_LEFT),
                $line
            );
        }

        parent::__construct(sprintf($message, $renderedLines), 0, $cause);
    }

    /**
     * Get the mock builder.
     *
     * @return MockBuilderInterface The mock builder.
     */
    public function mockBuilder()
    {
        return $this->mockBuilder;
    }

    /**
     * Get the generated source code.
     *
     * @return string The generated source code.
     */
    public function source()
    {
        return $this->source;
    }

    /**
     * Get the error details.
     *
     * @return array<string,mixed> The error details.
     */
    public function error()
    {
        return $this->error;
    }

    private $mockBuilder;
    private $source;
    private $error;
}
