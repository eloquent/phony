<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test\Properties;

class TestDerivedClassA extends TestBaseClass
{
    public function __construct(
        $publicValue = null,
        $privateValue = null,
        $protectedValue = null
    ) {
        if (null !== $publicValue) {
            $this->derivedPublic = $publicValue;
        }
        if (null !== $privateValue) {
            $this->derivedPrivate = $privateValue;
        }
        if (null !== $protectedValue) {
            $this->derivedProtected = $protectedValue;
        }
    }

    public $derivedPublic = '<derived-public>';
    private $derivedPrivate = '<derived-private>';
    private $basePrivate = '<derived-base-private>';
    protected $derivedProtected = '<derived-protected>';
}
