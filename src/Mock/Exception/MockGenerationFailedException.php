<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Exception;

use Eloquent\Phony\Mock\Builder\MockDefinition;
use Exception;
use Throwable;

/**
 * Mock generation failed.
 */
final class MockGenerationFailedException extends Exception implements
    MockException
{
    /**
     * Construct a mock generation failed exception.
     *
     * @param string               $className  The class name.
     * @param MockDefinition       $definition The definition.
     * @param string               $source     The generated source code.
     * @param ?array<string,mixed> $error      The error details.
     * @param ?Throwable           $cause      The cause, if available.
     */
    public function __construct(
        string $className,
        MockDefinition $definition,
        string $source,
        ?array $error,
        Throwable $cause = null
    ) {
        $this->definition = $definition;
        $this->source = $source;
        $this->error = $error;

        $lines = explode(PHP_EOL, $source);

        if (null === $error) {
            $message = sprintf(
                'Mock class %s generation failed.%sRelevant lines:%%s',
                $className,
                PHP_EOL
            );
            $errorLineNumber = null;
        } else {
            /** @var int */
            $errorLineNumber = $error['line'];
            $startLine = $errorLineNumber - 4;
            $contextLineCount = 7;

            if ($startLine < 0) {
                $contextLineCount += $startLine;
                $startLine = 0;
            }

            $lines = array_slice($lines, $startLine, $contextLineCount, true);

            $message = sprintf(
                'Mock class %s generation failed: ' .
                    '%s in generated code on line %d.%s' .
                    'Relevant lines:%%s',
                $className,
                $error['message'],
                $errorLineNumber,
                PHP_EOL
            );
        }

        end($lines);
        $lineNumber = key($lines);
        $padSize = strlen((string) ($lineNumber + 1)) + 4;
        $renderedLines = '';

        foreach ($lines as $lineNumber => $line) {
            if (null !== $errorLineNumber) {
                $highlight = $lineNumber + 1 === $errorLineNumber;
            } else {
                $highlight = false;
            }

            $renderedLines .= sprintf(
                '%s%s%s %s',
                PHP_EOL,
                str_pad(
                    (string) ($lineNumber + 1),
                    $padSize,
                    ' ',
                    STR_PAD_LEFT
                ),
                $highlight ? ':' : ' ',
                $line
            );
        }

        parent::__construct(sprintf($message, $renderedLines), 0, $cause);
    }

    /**
     * Get the definition.
     *
     * @return MockDefinition The definition.
     */
    public function definition(): MockDefinition
    {
        return $this->definition;
    }

    /**
     * Get the generated source code.
     *
     * @return string The generated source code.
     */
    public function source(): string
    {
        return $this->source;
    }

    /**
     * Get the error details.
     *
     * @return ?array<string,mixed> The error details.
     */
    public function error(): ?array
    {
        return $this->error;
    }

    /**
     * @var MockDefinition
     */
    private $definition;

    /**
     * @var string
     */
    private $source;

    /**
     * @var ?array<string,mixed>
     */
    private $error;
}
