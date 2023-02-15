<?php

declare(strict_types=1);

namespace Eloquent\Phony;

use Eloquent\Phony\Assertion\Exception\AssertionException;
use Eloquent\Phony\Test\Phony as TestPhony;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class VerificationTest extends TestCase
{
    protected function setUp(): void
    {
        TestPhony::setUseColor(true);
    }

    public function generateData()
    {
        $fixturePath = __DIR__ . '/../fixture/verification';
        $data = [];

        foreach (scandir($fixturePath) as $verification) {
            if ('.' === $verification[0]) {
                continue;
            }

            $verificationPath = $fixturePath . '/' . $verification;

            foreach (scandir($verificationPath) as $testName) {
                $testPath =
                    $fixturePath . '/' . $verification . '/' . $testName;

                if ('.' === $testName[0] || !is_dir($testPath)) {
                    continue;
                }

                $data[$verification . ' - ' . $testName] = [$verification, $testName];
            }
        }

        return $data;
    }

    /**
     * @dataProvider generateData
     */
    public function testVerification($verification, $testName)
    {
        $path = __DIR__ . '/../fixture/verification/' . $verification . '/' . $testName;

        if (is_file($path . '/supported.php')) {
            $isSupported = require $path . '/supported.php';

            if (!$isSupported) {
                $this->markTestSkipped($message);
            }
        }

        $expected = str_replace("\n", PHP_EOL, rtrim(file_get_contents($path . '/expected'), "\n"));
        TestPhony::reset();

        try {
            require $path . '/verification.php';

            $this->fail('Verification did not throw an exception');
        } catch (AssertionException $e) {
            $actual = $this->visualizeAnsi($e->getMessage());

            $this->assertSame($expected, $actual);

            $this->expectException(AssertionException::class);
            throw $e;
        }
    }

    private function visualizeAnsi($data)
    {
        return preg_replace_callback(
            '/(\x9B|\x1B\[)([0-?]*[ -\/]*[@-~])/',
            function ($matches) {
                if ("\033[" !== $matches[1]) {
                    throw new RuntimeException('Unexpected ANSI sequence.');
                }

                switch ($matches[2]) {
                    case '0m': return '%RESET%';
                    case '1m': return '%BOLD%';
                    case '2m': return '%FAINT%';
                    case '4m': return '%UNDERLINE%';

                    case '31m': return '%RED%';
                    case '32m': return '%GREEN%';
                    case '33m': return '%YELLOW%';
                    case '36m': return '%CYAN%';
                }

                throw new RuntimeException(sprintf('Unexpected ANSI code %s.', var_export($matches[2], true)));
            },
            $data
        );
    }
}
