<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test\Php81;

enum TestBackedEnum: string
{
    case A = 'A';
    case B = 'B';
    case C = 'C';
}
