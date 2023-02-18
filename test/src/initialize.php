<?php

declare(strict_types=1);

use Eloquent\Phony\Test\Facade\FacadeContainer;
use Eloquent\Phony\Test\Facade\Globals;

Globals::$container = new FacadeContainer();
