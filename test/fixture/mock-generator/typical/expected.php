<?php

namespace Phony\Test;

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Mock\Handle\StaticHandleRegistry;
use Eloquent\Phony\Mock\Mock;

class MockGeneratorTypical
extends \Eloquent\Phony\Test\TestClassB
implements Mock,
           \Iterator,
           \Countable,
           \ArrayAccess
{
    const CONSTANT_A = 'constantValueA';
    const CONSTANT_B = 444;
    const CONSTANT_C = null;

    public static function testClassAStaticMethodB(
        $first,
        $second,
        &$third = null
    ) {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        if ($¤argumentCount > 0) {
            $¤arguments[] = $first;
        }
        if ($¤argumentCount > 1) {
            $¤arguments[] = $second;
        }
        if ($¤argumentCount > 2) {
            $¤arguments[] = &$third;
        }
        for ($¤i = 3; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = \func_get_arg($¤i);
        }

        if (isset(StaticHandleRegistry::$handles['phony\\test\\mockgeneratortypical'])) {
            $¤result = StaticHandleRegistry::$handles['phony\\test\\mockgeneratortypical']->spy(__FUNCTION__)->invokeWith(
                new Arguments($¤arguments)
            );

            return $¤result;
        } else {
            $¤result = parent::testClassAStaticMethodB(...$¤arguments);

            return $¤result;
        }
    }

    public static function testClassBStaticMethodA()
    {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        for ($¤i = 0; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = \func_get_arg($¤i);
        }

        if (isset(StaticHandleRegistry::$handles['phony\\test\\mockgeneratortypical'])) {
            $¤result = StaticHandleRegistry::$handles['phony\\test\\mockgeneratortypical']->spy(__FUNCTION__)->invokeWith(
                new Arguments($¤arguments)
            );

            return $¤result;
        } else {
            $¤result = parent::testClassBStaticMethodA(...$¤arguments);

            return $¤result;
        }
    }

    public static function testClassBStaticMethodB(
        $first,
        $second
    ) {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        if ($¤argumentCount > 0) {
            $¤arguments[] = $first;
        }
        if ($¤argumentCount > 1) {
            $¤arguments[] = $second;
        }
        for ($¤i = 2; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = \func_get_arg($¤i);
        }

        if (isset(StaticHandleRegistry::$handles['phony\\test\\mockgeneratortypical'])) {
            $¤result = StaticHandleRegistry::$handles['phony\\test\\mockgeneratortypical']->spy(__FUNCTION__)->invokeWith(
                new Arguments($¤arguments)
            );

            return $¤result;
        } else {
            $¤result = parent::testClassBStaticMethodB(...$¤arguments);

            return $¤result;
        }
    }

    public static function testClassAStaticMethodA()
    {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        for ($¤i = 0; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = \func_get_arg($¤i);
        }

        if (isset(StaticHandleRegistry::$handles['phony\\test\\mockgeneratortypical'])) {
            $¤result = StaticHandleRegistry::$handles['phony\\test\\mockgeneratortypical']->spy(__FUNCTION__)->invokeWith(
                new Arguments($¤arguments)
            );

            return $¤result;
        } else {
            $¤result = parent::testClassAStaticMethodA(...$¤arguments);

            return $¤result;
        }
    }

    public static function methodA(
        $first,
        &$second
    ) {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        if ($¤argumentCount > 0) {
            $¤arguments[] = $first;
        }
        if ($¤argumentCount > 1) {
            $¤arguments[] = &$second;
        }
        for ($¤i = 2; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = \func_get_arg($¤i);
        }

        if (isset(StaticHandleRegistry::$handles['phony\\test\\mockgeneratortypical'])) {
            $¤result = StaticHandleRegistry::$handles['phony\\test\\mockgeneratortypical']->spy(__FUNCTION__)->invokeWith(
                new Arguments($¤arguments)
            );

            return $¤result;
        } else {
            $¤result = parent::methodA(...$¤arguments);

            return $¤result;
        }
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
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        if ($¤argumentCount > 0) {
            $¤arguments[] = $first;
        }
        if ($¤argumentCount > 1) {
            $¤arguments[] = $second;
        }
        if ($¤argumentCount > 2) {
            $¤arguments[] = $third;
        }
        if ($¤argumentCount > 3) {
            $¤arguments[] = $fourth;
        }
        if ($¤argumentCount > 4) {
            $¤arguments[] = $fifth;
        }
        for ($¤i = 5; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = \func_get_arg($¤i);
        }

        if (isset(StaticHandleRegistry::$handles['phony\\test\\mockgeneratortypical'])) {
            $¤result = StaticHandleRegistry::$handles['phony\\test\\mockgeneratortypical']->spy(__FUNCTION__)->invokeWith(
                new Arguments($¤arguments)
            );

            return $¤result;
        } else {
            $¤result = parent::methodB(...$¤arguments);

            return $¤result;
        }
    }

    public static function __callStatic(
        $name,
        array $arguments
    ) {
        $¤result = StaticHandleRegistry::$handles['phony\\test\\mockgeneratortypical']->spy($name)
            ->invokeWith(new Arguments($arguments));

        return $¤result;
    }

    public function __construct()
    {
    }

    public function testClassAMethodB(
        $first,
        $second,
        &$third = null
    ) {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        if ($¤argumentCount > 0) {
            $¤arguments[] = $first;
        }
        if ($¤argumentCount > 1) {
            $¤arguments[] = $second;
        }
        if ($¤argumentCount > 2) {
            $¤arguments[] = &$third;
        }
        for ($¤i = 3; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = \func_get_arg($¤i);
        }

        if (isset($this->_handle)) {
            $¤result = $this->_handle->spy(__FUNCTION__)->invokeWith(
                new Arguments($¤arguments)
            );

            return $¤result;
        } else {
            $¤result = parent::testClassAMethodB(...$¤arguments);

            return $¤result;
        }
    }

    public function testClassBMethodA()
    {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        for ($¤i = 0; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = \func_get_arg($¤i);
        }

        if (isset($this->_handle)) {
            $¤result = $this->_handle->spy(__FUNCTION__)->invokeWith(
                new Arguments($¤arguments)
            );

            return $¤result;
        } else {
            $¤result = parent::testClassBMethodA(...$¤arguments);

            return $¤result;
        }
    }

    public function testClassBMethodB(
        &$first,
        &$second
    ) {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        if ($¤argumentCount > 0) {
            $¤arguments[] = &$first;
        }
        if ($¤argumentCount > 1) {
            $¤arguments[] = &$second;
        }
        for ($¤i = 2; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = \func_get_arg($¤i);
        }

        if (isset($this->_handle)) {
            $¤result = $this->_handle->spy(__FUNCTION__)->invokeWith(
                new Arguments($¤arguments)
            );

            return $¤result;
        } else {
            $¤result = parent::testClassBMethodB(...$¤arguments);

            return $¤result;
        }
    }

    public function testClassAMethodA()
    {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        for ($¤i = 0; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = \func_get_arg($¤i);
        }

        if (isset($this->_handle)) {
            $¤result = $this->_handle->spy(__FUNCTION__)->invokeWith(
                new Arguments($¤arguments)
            );

            return $¤result;
        } else {
            $¤result = parent::testClassAMethodA(...$¤arguments);

            return $¤result;
        }
    }

    public function current() : mixed
    {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        for ($¤i = 0; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = \func_get_arg($¤i);
        }

        if (isset($this->_handle)) {
            $¤result = $this->_handle->spy(__FUNCTION__)->invokeWith(
                new Arguments($¤arguments)
            );

            return $¤result;
        } else {
            $¤result = parent::current(...$¤arguments);

            return $¤result;
        }
    }

    public function next() : void
    {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        for ($¤i = 0; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = \func_get_arg($¤i);
        }

        if (isset($this->_handle)) {
            $this->_handle->spy(__FUNCTION__)->invokeWith(
                new Arguments($¤arguments)
            );
        } else {
            parent::next(...$¤arguments);
        }
    }

    public function key() : mixed
    {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        for ($¤i = 0; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = \func_get_arg($¤i);
        }

        if (isset($this->_handle)) {
            $¤result = $this->_handle->spy(__FUNCTION__)->invokeWith(
                new Arguments($¤arguments)
            );

            return $¤result;
        } else {
            $¤result = parent::key(...$¤arguments);

            return $¤result;
        }
    }

    public function valid() : bool
    {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        for ($¤i = 0; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = \func_get_arg($¤i);
        }

        if (isset($this->_handle)) {
            $¤result = $this->_handle->spy(__FUNCTION__)->invokeWith(
                new Arguments($¤arguments)
            );

            return $¤result;
        } else {
            $¤result = parent::valid(...$¤arguments);

            return $¤result;
        }
    }

    public function rewind() : void
    {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        for ($¤i = 0; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = \func_get_arg($¤i);
        }

        if (isset($this->_handle)) {
            $this->_handle->spy(__FUNCTION__)->invokeWith(
                new Arguments($¤arguments)
            );
        } else {
            parent::rewind(...$¤arguments);
        }
    }

    public function count() : int
    {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        for ($¤i = 0; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = \func_get_arg($¤i);
        }

        if (isset($this->_handle)) {
            $¤result = $this->_handle->spy(__FUNCTION__)->invokeWith(
                new Arguments($¤arguments)
            );

            return $¤result;
        } else {
            $¤result = parent::count(...$¤arguments);

            return $¤result;
        }
    }

    public function offsetExists(
        $offset
    ) : bool {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        if ($¤argumentCount > 0) {
            $¤arguments[] = $offset;
        }
        for ($¤i = 1; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = \func_get_arg($¤i);
        }

        if (isset($this->_handle)) {
            $¤result = $this->_handle->spy(__FUNCTION__)->invokeWith(
                new Arguments($¤arguments)
            );

            return $¤result;
        } else {
            $¤result = parent::offsetExists(...$¤arguments);

            return $¤result;
        }
    }

    public function offsetGet(
        $offset
    ) : mixed {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        if ($¤argumentCount > 0) {
            $¤arguments[] = $offset;
        }
        for ($¤i = 1; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = \func_get_arg($¤i);
        }

        if (isset($this->_handle)) {
            $¤result = $this->_handle->spy(__FUNCTION__)->invokeWith(
                new Arguments($¤arguments)
            );

            return $¤result;
        } else {
            $¤result = parent::offsetGet(...$¤arguments);

            return $¤result;
        }
    }

    public function offsetSet(
        $offset,
        $value
    ) : void {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        if ($¤argumentCount > 0) {
            $¤arguments[] = $offset;
        }
        if ($¤argumentCount > 1) {
            $¤arguments[] = $value;
        }
        for ($¤i = 2; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = \func_get_arg($¤i);
        }

        if (isset($this->_handle)) {
            $this->_handle->spy(__FUNCTION__)->invokeWith(
                new Arguments($¤arguments)
            );
        } else {
            parent::offsetSet(...$¤arguments);
        }
    }

    public function offsetUnset(
        $offset
    ) : void {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        if ($¤argumentCount > 0) {
            $¤arguments[] = $offset;
        }
        for ($¤i = 1; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = \func_get_arg($¤i);
        }

        if (isset($this->_handle)) {
            $this->_handle->spy(__FUNCTION__)->invokeWith(
                new Arguments($¤arguments)
            );
        } else {
            parent::offsetUnset(...$¤arguments);
        }
    }

    public function methodC(
        \Eloquent\Phony\Test\TestClassA $first,
        ?\Eloquent\Phony\Test\TestClassA $second = null,
        array $third = array (
),
        ?array $fourth = null
    ) {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        if ($¤argumentCount > 0) {
            $¤arguments[] = $first;
        }
        if ($¤argumentCount > 1) {
            $¤arguments[] = $second;
        }
        if ($¤argumentCount > 2) {
            $¤arguments[] = $third;
        }
        if ($¤argumentCount > 3) {
            $¤arguments[] = $fourth;
        }
        for ($¤i = 4; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = \func_get_arg($¤i);
        }

        if (isset($this->_handle)) {
            $¤result = $this->_handle->spy(__FUNCTION__)->invokeWith(
                new Arguments($¤arguments)
            );

            return $¤result;
        } else {
            $¤result = parent::methodC(...$¤arguments);

            return $¤result;
        }
    }

    public function methodD()
    {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        for ($¤i = 0; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = \func_get_arg($¤i);
        }

        if (isset($this->_handle)) {
            $¤result = $this->_handle->spy(__FUNCTION__)->invokeWith(
                new Arguments($¤arguments)
            );

            return $¤result;
        } else {
            $¤result = parent::methodD(...$¤arguments);

            return $¤result;
        }
    }

    public function __call(
        $name,
        array $arguments
    ) {
        $¤result = $this->_handle->spy($name)
            ->invokeWith(new Arguments($arguments));

        return $¤result;
    }

    protected static function testClassAStaticMethodC()
    {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        for ($¤i = 0; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = \func_get_arg($¤i);
        }

        if (isset(StaticHandleRegistry::$handles['phony\\test\\mockgeneratortypical'])) {
            $¤result = StaticHandleRegistry::$handles['phony\\test\\mockgeneratortypical']->spy(__FUNCTION__)->invokeWith(
                new Arguments($¤arguments)
            );

            return $¤result;
        } else {
            $¤result = parent::testClassAStaticMethodC(...$¤arguments);

            return $¤result;
        }
    }

    protected static function testClassAStaticMethodD(
        $first,
        $second
    ) {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        if ($¤argumentCount > 0) {
            $¤arguments[] = $first;
        }
        if ($¤argumentCount > 1) {
            $¤arguments[] = $second;
        }
        for ($¤i = 2; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = \func_get_arg($¤i);
        }

        if (isset(StaticHandleRegistry::$handles['phony\\test\\mockgeneratortypical'])) {
            $¤result = StaticHandleRegistry::$handles['phony\\test\\mockgeneratortypical']->spy(__FUNCTION__)->invokeWith(
                new Arguments($¤arguments)
            );

            return $¤result;
        } else {
            $¤result = parent::testClassAStaticMethodD(...$¤arguments);

            return $¤result;
        }
    }

    protected function testClassAMethodC()
    {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        for ($¤i = 0; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = \func_get_arg($¤i);
        }

        if (isset($this->_handle)) {
            $¤result = $this->_handle->spy(__FUNCTION__)->invokeWith(
                new Arguments($¤arguments)
            );

            return $¤result;
        } else {
            $¤result = parent::testClassAMethodC(...$¤arguments);

            return $¤result;
        }
    }

    protected function testClassAMethodD(
        &$first,
        &$second
    ) {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        if ($¤argumentCount > 0) {
            $¤arguments[] = &$first;
        }
        if ($¤argumentCount > 1) {
            $¤arguments[] = &$second;
        }
        for ($¤i = 2; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = \func_get_arg($¤i);
        }

        if (isset($this->_handle)) {
            $¤result = $this->_handle->spy(__FUNCTION__)->invokeWith(
                new Arguments($¤arguments)
            );

            return $¤result;
        } else {
            $¤result = parent::testClassAMethodD(...$¤arguments);

            return $¤result;
        }
    }

    private static function _callParentStatic(
        $name,
        Arguments $arguments
    ) {
        return parent::$name(...$arguments->all());
    }

    private static function _callMagicStatic(
        $name,
        Arguments $arguments
    ) {
        return parent::__callStatic($name, $arguments->all());
    }

    private function _callParent(
        $name,
        Arguments $arguments
    ) {
        return parent::$name(...$arguments->all());
    }

    private function _callParentConstructor(
        Arguments $arguments
    ) {
        parent::__construct(...$arguments->all());
    }

    private function _callMagic(
        $name,
        Arguments $arguments
    ) {
        return parent::__call($name, $arguments->all());
    }

    public static $propertyA = 'valueA';
    public static $propertyB = 222;
    public $propertyC = 'valueC';
    public $propertyD = 333;
    private readonly InstanceHandle $_handle;
}
