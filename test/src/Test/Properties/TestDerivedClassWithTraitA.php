<?php

namespace Eloquent\Phony\Test\Properties;

class TestDerivedClassWithTraitA extends TestBaseClass
{
    use TestTrait;

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
    protected $derivedProtected = '<derived-protected>';
}
