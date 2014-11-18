<?php

class MockGeneratorDateTime
extends \DateTime
implements \Eloquent\Phony\Mock\MockInterface
{
    public static function __set_state()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return self::$_staticProxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public static function createFromFormat(
        $a0,
        $a1,
        $a2 = null
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }
        if ($argumentCount > 1) {
            $arguments[] = $a1;
        }
        if ($argumentCount > 2) {
            $arguments[] = $a2;
        }

        for ($i = 3; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return self::$_staticProxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public static function getLastErrors()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return self::$_staticProxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function __construct()
    {
    }

    public function __wakeup()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function format(
        $a0
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }

        for ($i = 1; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function modify(
        $a0
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }

        for ($i = 1; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function add(
        $a0
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }

        for ($i = 1; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function sub(
        $a0
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }

        for ($i = 1; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function getTimezone()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function setTimezone(
        $a0
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }

        for ($i = 1; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function getOffset()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function setTime(
        $a0,
        $a1,
        $a2 = null
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }
        if ($argumentCount > 1) {
            $arguments[] = $a1;
        }
        if ($argumentCount > 2) {
            $arguments[] = $a2;
        }

        for ($i = 3; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function setDate(
        $a0,
        $a1,
        $a2
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }
        if ($argumentCount > 1) {
            $arguments[] = $a1;
        }
        if ($argumentCount > 2) {
            $arguments[] = $a2;
        }

        for ($i = 3; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function setISODate(
        $a0,
        $a1,
        $a2 = null
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }
        if ($argumentCount > 1) {
            $arguments[] = $a1;
        }
        if ($argumentCount > 2) {
            $arguments[] = $a2;
        }

        for ($i = 3; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function setTimestamp(
        $a0
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }

        for ($i = 1; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function getTimestamp()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function diff(
        $a0,
        $a1 = null
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }
        if ($argumentCount > 1) {
            $arguments[] = $a1;
        }

        for ($i = 2; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    private static function _callParentStatic(
        $name,
        \Eloquent\Phony\Call\Argument\ArgumentsInterface $arguments
    ) {
        return \call_user_func_array(
            array(__CLASS__, 'parent::' . $name),
            $arguments->all()
        );
    }

    private function _callParent(
        $name,
        \Eloquent\Phony\Call\Argument\ArgumentsInterface $arguments
    ) {
        return \call_user_func_array(
            array($this, 'parent::' . $name),
            $arguments->all()
        );
    }

    private function _callParentConstructor(
        \Eloquent\Phony\Call\Argument\ArgumentsInterface $arguments
    ) {
        \call_user_func_array(
            array($this, 'parent::__construct'),
            $arguments->all()
        );
    }

    private static $_uncallableMethods = array();
    private static $_traitMethods = array();
    private static $_customMethods = array();
    private static $_staticProxy;
    private $_proxy;
}
