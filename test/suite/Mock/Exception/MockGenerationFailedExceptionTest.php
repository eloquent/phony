<?php

namespace Eloquent\Phony\Mock\Exception;

use Eloquent\Phony\Mock\Builder\MockDefinition;
use Exception;
use PHPUnit\Framework\TestCase;

class MockGenerationFailedExceptionTest extends TestCase
{
    protected function setUp()
    {
        $this->definition = new MockDefinition(
            [],
            [],
            [],
            [],
            [],
            [],
            'ClassName'
        );
        $this->cause = new Exception();
    }

    public function testException()
    {
        $source = <<<'EOD'
// this line is NOT context
// this line is context
// this line is context
// this line is context
ERROR
// this line is context
// this line is context
// this line is context
// this line is NOT context
EOD;
        $source = str_replace("\n", PHP_EOL, $source);
        $error = ['message' => 'errorMessage', 'line' => 5];
        $exception = new MockGenerationFailedException('ClassName', $this->definition, $source, $error, $this->cause);
        $expected = <<<'EOD'
Mock class ClassName generation failed: errorMessage in generated code on line 5.
Relevant lines:
    2  // this line is context
    3  // this line is context
    4  // this line is context
    5: ERROR
    6  // this line is context
    7  // this line is context
    8  // this line is context
EOD;
        $expected = str_replace("\n", PHP_EOL, $expected);

        $this->assertSame($this->definition, $exception->definition());
        $this->assertSame($source, $exception->source());
        $this->assertSame($error, $exception->error());
        $this->assertSame($expected, $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($this->cause, $exception->getPrevious());
    }

    public function testExceptionWithoutError()
    {
        $source = <<<'EOD'
// this line is NOT context
// this line is context
// this line is context
// this line is context
ERROR
// this line is context
// this line is context
// this line is context
// this line is NOT context
EOD;
        $source = str_replace("\n", PHP_EOL, $source);
        $error = null;
        $exception = new MockGenerationFailedException('ClassName', $this->definition, $source, $error, $this->cause);
        $expected = <<<'EOD'
Mock class ClassName generation failed.
Relevant lines:
    1  // this line is NOT context
    2  // this line is context
    3  // this line is context
    4  // this line is context
    5  ERROR
    6  // this line is context
    7  // this line is context
    8  // this line is context
    9  // this line is NOT context
EOD;
        $expected = str_replace("\n", PHP_EOL, $expected);

        $this->assertSame($this->definition, $exception->definition());
        $this->assertSame($source, $exception->source());
        $this->assertSame($error, $exception->error());
        $this->assertSame($expected, $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($this->cause, $exception->getPrevious());
    }

    public function testExceptionWithErrorAtStart()
    {
        $source = <<<'EOD'
// this line is context
ERROR
// this line is context
// this line is context
// this line is context
// this line is NOT context
EOD;
        $source = str_replace("\n", PHP_EOL, $source);
        $error = ['message' => 'errorMessage', 'line' => 2];
        $exception = new MockGenerationFailedException('ClassName', $this->definition, $source, $error, $this->cause);
        $expected = <<<'EOD'
Mock class ClassName generation failed: errorMessage in generated code on line 2.
Relevant lines:
    1  // this line is context
    2: ERROR
    3  // this line is context
    4  // this line is context
    5  // this line is context
EOD;
        $expected = str_replace("\n", PHP_EOL, $expected);

        $this->assertSame($this->definition, $exception->definition());
        $this->assertSame($source, $exception->source());
        $this->assertSame($error, $exception->error());
        $this->assertSame($expected, $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($this->cause, $exception->getPrevious());
    }

    public function testExceptionWithErrorAtEnd()
    {
        $source = <<<'EOD'
// this line is NOT context
// this line is context
// this line is context
// this line is context
ERROR
// this line is context
EOD;
        $source = str_replace("\n", PHP_EOL, $source);
        $error = ['message' => 'errorMessage', 'line' => 5];
        $exception = new MockGenerationFailedException('ClassName', $this->definition, $source, $error, $this->cause);
        $expected = <<<'EOD'
Mock class ClassName generation failed: errorMessage in generated code on line 5.
Relevant lines:
    2  // this line is context
    3  // this line is context
    4  // this line is context
    5: ERROR
    6  // this line is context
EOD;
        $expected = str_replace("\n", PHP_EOL, $expected);

        $this->assertSame($this->definition, $exception->definition());
        $this->assertSame($source, $exception->source());
        $this->assertSame($error, $exception->error());
        $this->assertSame($expected, $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($this->cause, $exception->getPrevious());
    }
}
