<?php

class MockGeneratorKeywordMethod
extends \AMQPQueue
implements \Eloquent\Phony\Mock\MockInterface
{
    public function __construct()
    {
    }

    public function getName()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function setName(
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

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function getFlags()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function setFlags(
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

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function getArgument(
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

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function getArguments()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function setArgument(
        $a0,
        $a1
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

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function setArguments(
        array $a0
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }

        for ($i = 1; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function declareQueue()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function bind(
        $a0,
        $a1 = null,
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

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function get(
        $a0 = null
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }

        for ($i = 1; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function consume(
        $a0,
        $a1 = null,
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

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function ack(
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

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function nack(
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

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function reject(
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

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function purge()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function cancel(
        $a0 = null
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }

        for ($i = 1; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function delete(
        $a0 = null
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }

        for ($i = 1; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function unbind(
        $a0,
        $a1 = null,
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

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function getChannel()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function getConnection()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function getConsumerTag()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
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
