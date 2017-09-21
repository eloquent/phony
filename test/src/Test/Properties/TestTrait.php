<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test\Properties;

trait TestTrait
{
    public $traitPublic = '<trait-public>';
    private $traitPrivate = '<trait-private>';
    protected $traitProtected = '<trait-protected>';
}
