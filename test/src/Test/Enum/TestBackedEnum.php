<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test\Enum;

enum TestBackedEnum: string
{
    case A = 'A';
    case B = 'B';
    case C = 'C';
}
