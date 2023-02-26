<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test\Php82;

readonly class TestClassReadonly
{
  public string $propertyA;
  public int $propertyB;

  public function __construct() {
    $this->propertyA = 'a';
    $this->propertyB = 111;
  }
}
