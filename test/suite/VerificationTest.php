<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony;

use Eloquent\Phony\Assertion\Exception\AssertionException;
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Test\Phony as TestPhony;
use PHPUnit_Framework_TestCase;
use RuntimeException;

class VerificationTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->featureDetector = FeatureDetector::instance();

        TestPhony::setUseColor(true);
    }

    public function generateData()
    {
        $fixturePath = __DIR__ . '/../fixture/verification';
        $data = array();

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

                $data[$verification . ' - ' . $testName] = array($verification, $testName);
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

        $detector = $this->featureDetector;

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

            $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
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
