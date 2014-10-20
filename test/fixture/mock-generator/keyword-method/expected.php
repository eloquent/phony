<?php

/**
 * A mock class generated by Phony.
 *
 * @uses \AMQPQueue
 *
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with the Phony source code.
 *
 * @link https://github.com/eloquent/phony
 */
class MockGeneratorKeywordMethod
extends \AMQPQueue
implements \Eloquent\Phony\Mock\MockInterface
{
    /**
     * Construct a mock.
     */
    public function __construct()
    {
    }

    /**
     * Inherited method 'ack'.
     *
     * @uses \AMQPQueue::ack()
     *
     * @param mixed $a0 Was 'delivery_tag'.
     * @param mixed $a1 Was 'flags'.
     */
    public function ack(
        $a0,
        $a1 = null
    ) {
        $arguments = array($a0, $a1);
        for ($i = 2; $i < func_num_args(); $i++) {
            $arguments[] = func_get_arg($i);
        }

        if (isset($this->_stubs[__FUNCTION__])) {
            return $this->_stubs[__FUNCTION__]->invokeWith(
                new \Eloquent\Phony\Call\Argument\Arguments($arguments)
            );
        }
    }

    /**
     * Inherited method 'bind'.
     *
     * @uses \AMQPQueue::bind()
     *
     * @param mixed $a0 Was 'exchange_name'.
     * @param mixed $a1 Was 'routing_key'.
     * @param mixed $a2 Was 'arguments'.
     */
    public function bind(
        $a0,
        $a1 = null,
        $a2 = null
    ) {
        $arguments = array($a0, $a1, $a2);
        for ($i = 3; $i < func_num_args(); $i++) {
            $arguments[] = func_get_arg($i);
        }

        if (isset($this->_stubs[__FUNCTION__])) {
            return $this->_stubs[__FUNCTION__]->invokeWith(
                new \Eloquent\Phony\Call\Argument\Arguments($arguments)
            );
        }
    }

    /**
     * Inherited method 'cancel'.
     *
     * @uses \AMQPQueue::cancel()
     *
     * @param mixed $a0 Was 'consumer_tag'.
     */
    public function cancel(
        $a0 = null
    ) {
        $arguments = array($a0);
        for ($i = 1; $i < func_num_args(); $i++) {
            $arguments[] = func_get_arg($i);
        }

        if (isset($this->_stubs[__FUNCTION__])) {
            return $this->_stubs[__FUNCTION__]->invokeWith(
                new \Eloquent\Phony\Call\Argument\Arguments($arguments)
            );
        }
    }

    /**
     * Inherited method 'consume'.
     *
     * @uses \AMQPQueue::consume()
     *
     * @param mixed $a0 Was 'callback'.
     * @param mixed $a1 Was 'flags'.
     * @param mixed $a2 Was 'consumer_tag'.
     */
    public function consume(
        $a0,
        $a1 = null,
        $a2 = null
    ) {
        $arguments = array($a0, $a1, $a2);
        for ($i = 3; $i < func_num_args(); $i++) {
            $arguments[] = func_get_arg($i);
        }

        if (isset($this->_stubs[__FUNCTION__])) {
            return $this->_stubs[__FUNCTION__]->invokeWith(
                new \Eloquent\Phony\Call\Argument\Arguments($arguments)
            );
        }
    }

    /**
     * Inherited method 'declareQueue'.
     *
     * @uses \AMQPQueue::declareQueue()
     */
    public function declareQueue()
    {
        $arguments = array();
        for ($i = 0; $i < func_num_args(); $i++) {
            $arguments[] = func_get_arg($i);
        }

        if (isset($this->_stubs[__FUNCTION__])) {
            return $this->_stubs[__FUNCTION__]->invokeWith(
                new \Eloquent\Phony\Call\Argument\Arguments($arguments)
            );
        }
    }

    /**
     * Inherited method 'delete'.
     *
     * @uses \AMQPQueue::delete()
     *
     * @param mixed $a0 Was 'flags'.
     */
    public function delete(
        $a0 = null
    ) {
        $arguments = array($a0);
        for ($i = 1; $i < func_num_args(); $i++) {
            $arguments[] = func_get_arg($i);
        }

        if (isset($this->_stubs[__FUNCTION__])) {
            return $this->_stubs[__FUNCTION__]->invokeWith(
                new \Eloquent\Phony\Call\Argument\Arguments($arguments)
            );
        }
    }

    /**
     * Inherited method 'get'.
     *
     * @uses \AMQPQueue::get()
     *
     * @param mixed $a0 Was 'flags'.
     */
    public function get(
        $a0 = null
    ) {
        $arguments = array($a0);
        for ($i = 1; $i < func_num_args(); $i++) {
            $arguments[] = func_get_arg($i);
        }

        if (isset($this->_stubs[__FUNCTION__])) {
            return $this->_stubs[__FUNCTION__]->invokeWith(
                new \Eloquent\Phony\Call\Argument\Arguments($arguments)
            );
        }
    }

    /**
     * Inherited method 'getArgument'.
     *
     * @uses \AMQPQueue::getArgument()
     *
     * @param mixed $a0 Was 'argument'.
     */
    public function getArgument(
        $a0
    ) {
        $arguments = array($a0);
        for ($i = 1; $i < func_num_args(); $i++) {
            $arguments[] = func_get_arg($i);
        }

        if (isset($this->_stubs[__FUNCTION__])) {
            return $this->_stubs[__FUNCTION__]->invokeWith(
                new \Eloquent\Phony\Call\Argument\Arguments($arguments)
            );
        }
    }

    /**
     * Inherited method 'getArguments'.
     *
     * @uses \AMQPQueue::getArguments()
     */
    public function getArguments()
    {
        $arguments = array();
        for ($i = 0; $i < func_num_args(); $i++) {
            $arguments[] = func_get_arg($i);
        }

        if (isset($this->_stubs[__FUNCTION__])) {
            return $this->_stubs[__FUNCTION__]->invokeWith(
                new \Eloquent\Phony\Call\Argument\Arguments($arguments)
            );
        }
    }

    /**
     * Inherited method 'getChannel'.
     *
     * @uses \AMQPQueue::getChannel()
     */
    public function getChannel()
    {
        $arguments = array();
        for ($i = 0; $i < func_num_args(); $i++) {
            $arguments[] = func_get_arg($i);
        }

        if (isset($this->_stubs[__FUNCTION__])) {
            return $this->_stubs[__FUNCTION__]->invokeWith(
                new \Eloquent\Phony\Call\Argument\Arguments($arguments)
            );
        }
    }

    /**
     * Inherited method 'getConnection'.
     *
     * @uses \AMQPQueue::getConnection()
     */
    public function getConnection()
    {
        $arguments = array();
        for ($i = 0; $i < func_num_args(); $i++) {
            $arguments[] = func_get_arg($i);
        }

        if (isset($this->_stubs[__FUNCTION__])) {
            return $this->_stubs[__FUNCTION__]->invokeWith(
                new \Eloquent\Phony\Call\Argument\Arguments($arguments)
            );
        }
    }

    /**
     * Inherited method 'getFlags'.
     *
     * @uses \AMQPQueue::getFlags()
     */
    public function getFlags()
    {
        $arguments = array();
        for ($i = 0; $i < func_num_args(); $i++) {
            $arguments[] = func_get_arg($i);
        }

        if (isset($this->_stubs[__FUNCTION__])) {
            return $this->_stubs[__FUNCTION__]->invokeWith(
                new \Eloquent\Phony\Call\Argument\Arguments($arguments)
            );
        }
    }

    /**
     * Inherited method 'getName'.
     *
     * @uses \AMQPQueue::getName()
     */
    public function getName()
    {
        $arguments = array();
        for ($i = 0; $i < func_num_args(); $i++) {
            $arguments[] = func_get_arg($i);
        }

        if (isset($this->_stubs[__FUNCTION__])) {
            return $this->_stubs[__FUNCTION__]->invokeWith(
                new \Eloquent\Phony\Call\Argument\Arguments($arguments)
            );
        }
    }

    /**
     * Inherited method 'nack'.
     *
     * @uses \AMQPQueue::nack()
     *
     * @param mixed $a0 Was 'delivery_tag'.
     * @param mixed $a1 Was 'flags'.
     */
    public function nack(
        $a0,
        $a1 = null
    ) {
        $arguments = array($a0, $a1);
        for ($i = 2; $i < func_num_args(); $i++) {
            $arguments[] = func_get_arg($i);
        }

        if (isset($this->_stubs[__FUNCTION__])) {
            return $this->_stubs[__FUNCTION__]->invokeWith(
                new \Eloquent\Phony\Call\Argument\Arguments($arguments)
            );
        }
    }

    /**
     * Inherited method 'purge'.
     *
     * @uses \AMQPQueue::purge()
     */
    public function purge()
    {
        $arguments = array();
        for ($i = 0; $i < func_num_args(); $i++) {
            $arguments[] = func_get_arg($i);
        }

        if (isset($this->_stubs[__FUNCTION__])) {
            return $this->_stubs[__FUNCTION__]->invokeWith(
                new \Eloquent\Phony\Call\Argument\Arguments($arguments)
            );
        }
    }

    /**
     * Inherited method 'reject'.
     *
     * @uses \AMQPQueue::reject()
     *
     * @param mixed $a0 Was 'delivery_tag'.
     * @param mixed $a1 Was 'flags'.
     */
    public function reject(
        $a0,
        $a1 = null
    ) {
        $arguments = array($a0, $a1);
        for ($i = 2; $i < func_num_args(); $i++) {
            $arguments[] = func_get_arg($i);
        }

        if (isset($this->_stubs[__FUNCTION__])) {
            return $this->_stubs[__FUNCTION__]->invokeWith(
                new \Eloquent\Phony\Call\Argument\Arguments($arguments)
            );
        }
    }

    /**
     * Inherited method 'setArgument'.
     *
     * @uses \AMQPQueue::setArgument()
     *
     * @param mixed $a0 Was 'key'.
     * @param mixed $a1 Was 'value'.
     */
    public function setArgument(
        $a0,
        $a1
    ) {
        $arguments = array($a0, $a1);
        for ($i = 2; $i < func_num_args(); $i++) {
            $arguments[] = func_get_arg($i);
        }

        if (isset($this->_stubs[__FUNCTION__])) {
            return $this->_stubs[__FUNCTION__]->invokeWith(
                new \Eloquent\Phony\Call\Argument\Arguments($arguments)
            );
        }
    }

    /**
     * Inherited method 'setArguments'.
     *
     * @uses \AMQPQueue::setArguments()
     *
     * @param array $a0 Was 'arguments'.
     */
    public function setArguments(
        array $a0
    ) {
        $arguments = array($a0);
        for ($i = 1; $i < func_num_args(); $i++) {
            $arguments[] = func_get_arg($i);
        }

        if (isset($this->_stubs[__FUNCTION__])) {
            return $this->_stubs[__FUNCTION__]->invokeWith(
                new \Eloquent\Phony\Call\Argument\Arguments($arguments)
            );
        }
    }

    /**
     * Inherited method 'setFlags'.
     *
     * @uses \AMQPQueue::setFlags()
     *
     * @param mixed $a0 Was 'flags'.
     */
    public function setFlags(
        $a0
    ) {
        $arguments = array($a0);
        for ($i = 1; $i < func_num_args(); $i++) {
            $arguments[] = func_get_arg($i);
        }

        if (isset($this->_stubs[__FUNCTION__])) {
            return $this->_stubs[__FUNCTION__]->invokeWith(
                new \Eloquent\Phony\Call\Argument\Arguments($arguments)
            );
        }
    }

    /**
     * Inherited method 'setName'.
     *
     * @uses \AMQPQueue::setName()
     *
     * @param mixed $a0 Was 'queue_name'.
     */
    public function setName(
        $a0
    ) {
        $arguments = array($a0);
        for ($i = 1; $i < func_num_args(); $i++) {
            $arguments[] = func_get_arg($i);
        }

        if (isset($this->_stubs[__FUNCTION__])) {
            return $this->_stubs[__FUNCTION__]->invokeWith(
                new \Eloquent\Phony\Call\Argument\Arguments($arguments)
            );
        }
    }

    /**
     * Inherited method 'unbind'.
     *
     * @uses \AMQPQueue::unbind()
     *
     * @param mixed $a0 Was 'exchange_name'.
     * @param mixed $a1 Was 'routing_key'.
     * @param mixed $a2 Was 'arguments'.
     */
    public function unbind(
        $a0,
        $a1 = null,
        $a2 = null
    ) {
        $arguments = array($a0, $a1, $a2);
        for ($i = 3; $i < func_num_args(); $i++) {
            $arguments[] = func_get_arg($i);
        }

        if (isset($this->_stubs[__FUNCTION__])) {
            return $this->_stubs[__FUNCTION__]->invokeWith(
                new \Eloquent\Phony\Call\Argument\Arguments($arguments)
            );
        }
    }

    /**
     * Call a static parent method.
     *
     * @param string                                           $name      The method name.
     * @param \Eloquent\Phony\Call\Argument\ArgumentsInterface $arguments The arguments.
     */
    private static function _callParentStatic(
        $name,
        \Eloquent\Phony\Call\Argument\ArgumentsInterface $arguments
    ) {
        return call_user_func_array(
            array(__CLASS__, 'parent::' . $name),
            $arguments->all()
        );
    }

    /**
     * Call a parent method.
     *
     * @param string                                           $name      The method name.
     * @param \Eloquent\Phony\Call\Argument\ArgumentsInterface $arguments The arguments.
     */
    private function _callParent(
        $name,
        \Eloquent\Phony\Call\Argument\ArgumentsInterface $arguments
    ) {
        return call_user_func_array(
            array($this, 'parent::' . $name),
            $arguments->all()
        );
    }

    private static $_staticStubs = array();
    private static $_magicStaticStubs = array();
    private $_stubs = array();
    private $_magicStubs = array();
    private $_mockId;
}
