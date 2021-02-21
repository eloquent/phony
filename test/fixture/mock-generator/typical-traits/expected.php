<?php

namespace Phony\Test;

class MockGeneratorTypicalTraits
extends \Eloquent\Phony\Test\TestClassB
implements \Eloquent\Phony\Mock\Mock,
           \Iterator,
           \Countable,
           \ArrayAccess
{
    use \Eloquent\Phony\Test\TestTraitA,
        \Eloquent\Phony\Test\TestTraitB
    {
        \Eloquent\Phony\Test\TestTraitA::testClassAStaticMethodA
            as private _callTrait_Eloquent¦Phony¦Test¦TestTraitA»testClassAStaticMethodA;
        \Eloquent\Phony\Test\TestTraitA::testClassAMethodB
            as private _callTrait_Eloquent¦Phony¦Test¦TestTraitA»testClassAMethodB;
        \Eloquent\Phony\Test\TestTraitB::testClassAMethodB
            as private _callTrait_Eloquent¦Phony¦Test¦TestTraitB»testClassAMethodB;
        \Eloquent\Phony\Test\TestTraitB::testTraitBMethodA
            as private _callTrait_Eloquent¦Phony¦Test¦TestTraitB»testTraitBMethodA;
        \Eloquent\Phony\Test\TestTraitB::testClassAStaticMethodA
            as private _callTrait_Eloquent¦Phony¦Test¦TestTraitB»testClassAStaticMethodA;
    }

    const CONSTANT_A = 'constantValueA';
    const CONSTANT_B = 444;
    const CONSTANT_C = null;

    public static function testClassAStaticMethodB(
        $first,
        $second,
        &$third = null
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $first;
        }
        if ($¢argumentCount > 1) {
            $¢arguments[] = $second;
        }
        if ($¢argumentCount > 2) {
            $¢arguments[] = &$third;
        }

        for ($¢i = 3; $¢i < $¢argumentCount; ++$¢i) {
            $¢arguments[] = \func_get_arg($¢i);
        }

        if (!self::$_staticHandle) {
            $¢result = parent::testClassAStaticMethodB(...$¢arguments);

            return $¢result;
        }

        $¢result = self::$_staticHandle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $¢result;
    }

    public static function testClassBStaticMethodA()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($¢i = 0; $¢i < $¢argumentCount; ++$¢i) {
            $¢arguments[] = \func_get_arg($¢i);
        }

        if (!self::$_staticHandle) {
            $¢result = parent::testClassBStaticMethodA(...$¢arguments);

            return $¢result;
        }

        $¢result = self::$_staticHandle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $¢result;
    }

    public static function testClassBStaticMethodB(
        $first,
        $second
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $first;
        }
        if ($¢argumentCount > 1) {
            $¢arguments[] = $second;
        }

        for ($¢i = 2; $¢i < $¢argumentCount; ++$¢i) {
            $¢arguments[] = \func_get_arg($¢i);
        }

        if (!self::$_staticHandle) {
            $¢result = parent::testClassBStaticMethodB(...$¢arguments);

            return $¢result;
        }

        $¢result = self::$_staticHandle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $¢result;
    }

    public static function testClassAStaticMethodA()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($¢i = 0; $¢i < $¢argumentCount; ++$¢i) {
            $¢arguments[] = \func_get_arg($¢i);
        }

        if (!self::$_staticHandle) {
            $¢result = parent::testClassAStaticMethodA(...$¢arguments);

            return $¢result;
        }

        $¢result = self::$_staticHandle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $¢result;
    }

    public static function methodA(
        $first,
        &$second
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $first;
        }
        if ($¢argumentCount > 1) {
            $¢arguments[] = &$second;
        }

        for ($¢i = 2; $¢i < $¢argumentCount; ++$¢i) {
            $¢arguments[] = \func_get_arg($¢i);
        }

        if (!self::$_staticHandle) {
            $¢result = parent::methodA(...$¢arguments);

            return $¢result;
        }

        $¢result = self::$_staticHandle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $¢result;
    }

    public static function methodB(
        $first = null,
        $second = 111,
        $third = array (
),
        $fourth = array (
  0 => 'valueA',
  1 => 'valueB',
),
        $fifth = array (
  'keyA' => 'valueA',
  'keyB' => 'valueB',
)
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $first;
        }
        if ($¢argumentCount > 1) {
            $¢arguments[] = $second;
        }
        if ($¢argumentCount > 2) {
            $¢arguments[] = $third;
        }
        if ($¢argumentCount > 3) {
            $¢arguments[] = $fourth;
        }
        if ($¢argumentCount > 4) {
            $¢arguments[] = $fifth;
        }

        for ($¢i = 5; $¢i < $¢argumentCount; ++$¢i) {
            $¢arguments[] = \func_get_arg($¢i);
        }

        if (!self::$_staticHandle) {
            $¢result = parent::methodB(...$¢arguments);

            return $¢result;
        }

        $¢result = self::$_staticHandle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $¢result;
    }

    public static function __callStatic(
        $name,
        array $arguments
    ) {
        $¢result = self::$_staticHandle
            ->spy($name)
            ->invokeWith(
                new \Eloquent\Phony\Call\Arguments($arguments)
            );

        return $¢result;
    }

    public function __construct()
    {
    }

    public function testClassAMethodB(
        $first,
        $second,
        &$third = null
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $first;
        }
        if ($¢argumentCount > 1) {
            $¢arguments[] = $second;
        }
        if ($¢argumentCount > 2) {
            $¢arguments[] = &$third;
        }

        for ($¢i = 3; $¢i < $¢argumentCount; ++$¢i) {
            $¢arguments[] = \func_get_arg($¢i);
        }

        if (!$this->_handle) {
            $¢result = parent::testClassAMethodB(...$¢arguments);

            return $¢result;
        }

        $¢result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $¢result;
    }

    public function testClassBMethodA()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($¢i = 0; $¢i < $¢argumentCount; ++$¢i) {
            $¢arguments[] = \func_get_arg($¢i);
        }

        if (!$this->_handle) {
            $¢result = parent::testClassBMethodA(...$¢arguments);

            return $¢result;
        }

        $¢result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $¢result;
    }

    public function testClassBMethodB(
        &$first,
        &$second
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = &$first;
        }
        if ($¢argumentCount > 1) {
            $¢arguments[] = &$second;
        }

        for ($¢i = 2; $¢i < $¢argumentCount; ++$¢i) {
            $¢arguments[] = \func_get_arg($¢i);
        }

        if (!$this->_handle) {
            $¢result = parent::testClassBMethodB(...$¢arguments);

            return $¢result;
        }

        $¢result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $¢result;
    }

    public function testClassAMethodA()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($¢i = 0; $¢i < $¢argumentCount; ++$¢i) {
            $¢arguments[] = \func_get_arg($¢i);
        }

        if (!$this->_handle) {
            $¢result = parent::testClassAMethodA(...$¢arguments);

            return $¢result;
        }

        $¢result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $¢result;
    }

    public function testTraitBMethodA()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($¢i = 0; $¢i < $¢argumentCount; ++$¢i) {
            $¢arguments[] = \func_get_arg($¢i);
        }

        if (!$this->_handle) {
            $¢result = parent::testTraitBMethodA(...$¢arguments);

            return $¢result;
        }

        $¢result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $¢result;
    }

    public function current()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($¢i = 0; $¢i < $¢argumentCount; ++$¢i) {
            $¢arguments[] = \func_get_arg($¢i);
        }

        if (!$this->_handle) {
            $¢result = parent::current(...$¢arguments);

            return $¢result;
        }

        $¢result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $¢result;
    }

    public function next()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($¢i = 0; $¢i < $¢argumentCount; ++$¢i) {
            $¢arguments[] = \func_get_arg($¢i);
        }

        if (!$this->_handle) {
            $¢result = parent::next(...$¢arguments);

            return $¢result;
        }

        $¢result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $¢result;
    }

    public function key()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($¢i = 0; $¢i < $¢argumentCount; ++$¢i) {
            $¢arguments[] = \func_get_arg($¢i);
        }

        if (!$this->_handle) {
            $¢result = parent::key(...$¢arguments);

            return $¢result;
        }

        $¢result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $¢result;
    }

    public function valid()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($¢i = 0; $¢i < $¢argumentCount; ++$¢i) {
            $¢arguments[] = \func_get_arg($¢i);
        }

        if (!$this->_handle) {
            $¢result = parent::valid(...$¢arguments);

            return $¢result;
        }

        $¢result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $¢result;
    }

    public function rewind()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($¢i = 0; $¢i < $¢argumentCount; ++$¢i) {
            $¢arguments[] = \func_get_arg($¢i);
        }

        if (!$this->_handle) {
            $¢result = parent::rewind(...$¢arguments);

            return $¢result;
        }

        $¢result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $¢result;
    }

    public function count()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($¢i = 0; $¢i < $¢argumentCount; ++$¢i) {
            $¢arguments[] = \func_get_arg($¢i);
        }

        if (!$this->_handle) {
            $¢result = parent::count(...$¢arguments);

            return $¢result;
        }

        $¢result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $¢result;
    }

    public function offsetExists(
        $offset
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $offset;
        }

        for ($¢i = 1; $¢i < $¢argumentCount; ++$¢i) {
            $¢arguments[] = \func_get_arg($¢i);
        }

        if (!$this->_handle) {
            $¢result = parent::offsetExists(...$¢arguments);

            return $¢result;
        }

        $¢result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $¢result;
    }

    public function offsetGet(
        $offset
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $offset;
        }

        for ($¢i = 1; $¢i < $¢argumentCount; ++$¢i) {
            $¢arguments[] = \func_get_arg($¢i);
        }

        if (!$this->_handle) {
            $¢result = parent::offsetGet(...$¢arguments);

            return $¢result;
        }

        $¢result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $¢result;
    }

    public function offsetSet(
        $offset,
        $value
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $offset;
        }
        if ($¢argumentCount > 1) {
            $¢arguments[] = $value;
        }

        for ($¢i = 2; $¢i < $¢argumentCount; ++$¢i) {
            $¢arguments[] = \func_get_arg($¢i);
        }

        if (!$this->_handle) {
            $¢result = parent::offsetSet(...$¢arguments);

            return $¢result;
        }

        $¢result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $¢result;
    }

    public function offsetUnset(
        $offset
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $offset;
        }

        for ($¢i = 1; $¢i < $¢argumentCount; ++$¢i) {
            $¢arguments[] = \func_get_arg($¢i);
        }

        if (!$this->_handle) {
            $¢result = parent::offsetUnset(...$¢arguments);

            return $¢result;
        }

        $¢result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $¢result;
    }

    public function methodC(
        \Eloquent\Phony\Test\TestClassA $first,
        ?\Eloquent\Phony\Test\TestClassA $second = null,
        array $third = array (
),
        ?array $fourth = null
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $first;
        }
        if ($¢argumentCount > 1) {
            $¢arguments[] = $second;
        }
        if ($¢argumentCount > 2) {
            $¢arguments[] = $third;
        }
        if ($¢argumentCount > 3) {
            $¢arguments[] = $fourth;
        }

        for ($¢i = 4; $¢i < $¢argumentCount; ++$¢i) {
            $¢arguments[] = \func_get_arg($¢i);
        }

        if (!$this->_handle) {
            $¢result = parent::methodC(...$¢arguments);

            return $¢result;
        }

        $¢result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $¢result;
    }

    public function methodD()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($¢i = 0; $¢i < $¢argumentCount; ++$¢i) {
            $¢arguments[] = \func_get_arg($¢i);
        }

        if (!$this->_handle) {
            $¢result = parent::methodD(...$¢arguments);

            return $¢result;
        }

        $¢result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $¢result;
    }

    public function __call(
        $name,
        array $arguments
    ) {
        $¢result = $this->_handle
            ->spy($name)
            ->invokeWith(
                new \Eloquent\Phony\Call\Arguments($arguments)
            );

        return $¢result;
    }

    protected static function testClassAStaticMethodC()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($¢i = 0; $¢i < $¢argumentCount; ++$¢i) {
            $¢arguments[] = \func_get_arg($¢i);
        }

        if (!self::$_staticHandle) {
            $¢result = parent::testClassAStaticMethodC(...$¢arguments);

            return $¢result;
        }

        $¢result = self::$_staticHandle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $¢result;
    }

    protected static function testClassAStaticMethodD(
        $first,
        $second
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $first;
        }
        if ($¢argumentCount > 1) {
            $¢arguments[] = $second;
        }

        for ($¢i = 2; $¢i < $¢argumentCount; ++$¢i) {
            $¢arguments[] = \func_get_arg($¢i);
        }

        if (!self::$_staticHandle) {
            $¢result = parent::testClassAStaticMethodD(...$¢arguments);

            return $¢result;
        }

        $¢result = self::$_staticHandle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $¢result;
    }

    protected function testClassAMethodC()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($¢i = 0; $¢i < $¢argumentCount; ++$¢i) {
            $¢arguments[] = \func_get_arg($¢i);
        }

        if (!$this->_handle) {
            $¢result = parent::testClassAMethodC(...$¢arguments);

            return $¢result;
        }

        $¢result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $¢result;
    }

    protected function testClassAMethodD(
        &$first,
        &$second
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = &$first;
        }
        if ($¢argumentCount > 1) {
            $¢arguments[] = &$second;
        }

        for ($¢i = 2; $¢i < $¢argumentCount; ++$¢i) {
            $¢arguments[] = \func_get_arg($¢i);
        }

        if (!$this->_handle) {
            $¢result = parent::testClassAMethodD(...$¢arguments);

            return $¢result;
        }

        $¢result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $¢result;
    }

    private static function _callParentStatic(
        $name,
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        return parent::$name(...$arguments->all());
    }

    private static function _callTraitStatic(
        $traitName,
        $name,
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        $name = '_callTrait_' .
            \str_replace('\\', "\u{a6}", $traitName) .
            "\u{bb}" .
            $name;

        return self::$name(...$arguments->all());
    }

    private static function _callMagicStatic(
        $name,
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        return parent::__callStatic($name, $arguments->all());
    }

    private function _callParent(
        $name,
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        return parent::$name(...$arguments->all());
    }

    private function _callParentConstructor(
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        parent::__construct(...$arguments->all());
    }

    private function _callTrait(
        $traitName,
        $name,
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        $name = '_callTrait_' .
            \str_replace('\\', "\u{a6}", $traitName) .
            "\u{bb}" .
            $name;

        return $this->$name(...$arguments->all());
    }

    private function _callMagic(
        $name,
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        return parent::__call($name, $arguments->all());
    }

    public static $propertyA = 'valueA';
    public static $propertyB = 222;
    public $propertyC = 'valueC';
    public $propertyD = 333;
    private static $_uncallableMethods = array (
  'current' => true,
  'next' => true,
  'key' => true,
  'valid' => true,
  'rewind' => true,
  'count' => true,
  'offsetexists' => true,
  'offsetget' => true,
  'offsetset' => true,
  'offsetunset' => true,
);
    private static $_traitMethods = array (
  'testtraitbmethoda' => 'Eloquent\\Phony\\Test\\TestTraitB',
);
    private static $_customMethods = [];
    private static $_staticHandle;
    private $_handle;
}
