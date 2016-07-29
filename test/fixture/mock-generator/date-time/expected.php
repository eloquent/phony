<?php

class MockGeneratorDateTime
extends \DateTime
implements \Eloquent\Phony\Mock\Mock
{
    public static function __set_state()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if (!self::$_staticHandle) {
            $result = \call_user_func_array(
                array(__CLASS__, 'parent::' . '__set_state'),
                $arguments
            );

            return $result;
        }

        $result = self::$_staticHandle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($arguments)
        );

        return $result;
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

        for ($i = 3; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if (!self::$_staticHandle) {
            $result = \call_user_func_array(
                array(__CLASS__, 'parent::' . 'createFromFormat'),
                $arguments
            );

            return $result;
        }

        $result = self::$_staticHandle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($arguments)
        );

        return $result;
    }

    public static function getLastErrors()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if (!self::$_staticHandle) {
            $result = \call_user_func_array(
                array(__CLASS__, 'parent::' . 'getLastErrors'),
                $arguments
            );

            return $result;
        }

        $result = self::$_staticHandle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($arguments)
        );

        return $result;
    }

    public function __construct()
    {
    }

    public function __wakeup()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = \call_user_func_array(
                array($this, 'parent::' . '__wakeup'),
                $arguments
            );

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($arguments)
        );

        return $result;
    }

    public function format(
        $a0
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }

        for ($i = 1; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = \call_user_func_array(
                array($this, 'parent::' . 'format'),
                $arguments
            );

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($arguments)
        );

        return $result;
    }

    public function modify(
        $a0
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }

        for ($i = 1; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = \call_user_func_array(
                array($this, 'parent::' . 'modify'),
                $arguments
            );

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($arguments)
        );

        return $result;
    }

    public function add(
        $a0
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }

        for ($i = 1; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = \call_user_func_array(
                array($this, 'parent::' . 'add'),
                $arguments
            );

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($arguments)
        );

        return $result;
    }

    public function sub(
        $a0
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }

        for ($i = 1; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = \call_user_func_array(
                array($this, 'parent::' . 'sub'),
                $arguments
            );

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($arguments)
        );

        return $result;
    }

    public function getTimezone()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = \call_user_func_array(
                array($this, 'parent::' . 'getTimezone'),
                $arguments
            );

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($arguments)
        );

        return $result;
    }

    public function setTimezone(
        $a0
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }

        for ($i = 1; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = \call_user_func_array(
                array($this, 'parent::' . 'setTimezone'),
                $arguments
            );

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($arguments)
        );

        return $result;
    }

    public function getOffset()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = \call_user_func_array(
                array($this, 'parent::' . 'getOffset'),
                $arguments
            );

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($arguments)
        );

        return $result;
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

        for ($i = 3; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = \call_user_func_array(
                array($this, 'parent::' . 'setTime'),
                $arguments
            );

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($arguments)
        );

        return $result;
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

        for ($i = 3; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = \call_user_func_array(
                array($this, 'parent::' . 'setDate'),
                $arguments
            );

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($arguments)
        );

        return $result;
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

        for ($i = 3; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = \call_user_func_array(
                array($this, 'parent::' . 'setISODate'),
                $arguments
            );

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($arguments)
        );

        return $result;
    }

    public function setTimestamp(
        $a0
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }

        for ($i = 1; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = \call_user_func_array(
                array($this, 'parent::' . 'setTimestamp'),
                $arguments
            );

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($arguments)
        );

        return $result;
    }

    public function getTimestamp()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = \call_user_func_array(
                array($this, 'parent::' . 'getTimestamp'),
                $arguments
            );

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($arguments)
        );

        return $result;
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

        for ($i = 2; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = \call_user_func_array(
                array($this, 'parent::' . 'diff'),
                $arguments
            );

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($arguments)
        );

        return $result;
    }

    private static function _callParentStatic(
        $name,
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        return \call_user_func_array(
            array(__CLASS__, 'parent::' . $name),
            $arguments->all()
        );
    }

    private function _callParent(
        $name,
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        return \call_user_func_array(
            array($this, 'parent::' . $name),
            $arguments->all()
        );
    }

    private function _callParentConstructor(
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        \call_user_func_array(
            array($this, 'parent::__construct'),
            $arguments->all()
        );
    }

    private static $_uncallableMethods = array();
    private static $_traitMethods = array();
    private static $_customMethods = array();
    private static $_staticHandle;
    private $_handle;
}
