<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

use RuntimeException;

class TestException extends RuntimeException
{
  public mixed $arbitrary;
}
