<?php

declare(strict_types=1);

namespace Eloquent\Phony\Facade;

use PHPUnit\Framework\TestCase;

class FacadeContainerTest extends TestCase
{
    public function testContainer()
    {
        $error = null;

        try {
            new FacadeContainer();
        } catch (Throwable $e) {
            $error = $e;
        }

        $this->assertNull($error);
    }
}
